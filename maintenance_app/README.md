# TMQ — Field Trouble Management App

TMQ (現場トラブル管理アプリ) is a Docker-based web application for recording plant field troubles, running multi-step approval workflows, issuing maintenance request PDFs, tracking group TODOs, and recommending inspection cycles via a Python analytics engine.

**Stack:** Laravel 12 (PHP 8.2) · FastAPI (Python 3.10) · MySQL 8.0 · Dompdf · PHPUnit Feature tests

---

## Table of contents

1. [Overview](#overview)
2. [Features](#features)
3. [Architecture](#architecture)
4. [Quick start](#quick-start)
5. [Screens & routes](#screens--routes)
6. [Database](#database)
7. [Workflows](#workflows)
8. [Authentication & authorization](#authentication--authorization)
9. [Notifications](#notifications)
10. [Python Engine](#python-engine)
11. [Testing](#testing)
12. [Directory structure](#directory-structure)
13. [Useful commands](#useful-commands)

---

## Overview

TMQ supports plant operations and maintenance teams through the full lifecycle of a field incident:

1. **Register** a trouble (with optional browser speech input)
2. **Approve** it through an operations → maintenance workflow
3. **Download** a maintenance request form as PDF
4. **Submit** a repair completion report and approve that workflow
5. **Track** group TODOs created automatically on registration/approval
6. **Analyze** trouble frequency by category and get **recommended inspection cycles** from Python

All application pages (except login) require authentication. Approvals are restricted by user **role** and **group**.

---

## Features

| Area | What it does |
|------|----------------|
| Trouble registration | Create incidents with major/middle/minor categories, dates, spare parts, drawings, reporter/group |
| Speech input | HTML5 SpeechRecognition buttons on textareas (Chrome / Edge) |
| TODO sync | On register/approve, create/update group TODO items linked to the trouble |
| Approval workflow (trouble) | Discoverer → Leader → Ops manager → Maintenance leader → Maintenance manager → Completed |
| Approval workflow (repair) | Maintenance staff → Leader → Manager → Ops leader → Ops manager → Completed |
| Notifications | Mail + Microsoft Teams webhook on each workflow step |
| Maintenance request PDF | Dompdf “保全依頼表” download with Japanese fonts |
| Repair completion report | Form + save to `repair_reports`, then repair approval flow |
| Analytics | Aggregate counts / frequency by category |
| Inspection planning | Laravel → Python API merges aggregates with monitoring data and returns recommended cycle & points |
| Security | Auth middleware, policies (role + group), `lockForUpdate` on concurrent approvals |
| Tests | PHPUnit Feature suite (auth, workflow, concurrency, PDF, Python) |

---

## Architecture

```
Browser
   │
   ▼
┌─────────────────────┐      HTTP       ┌──────────────────────┐
│  app (Laravel)      │ ──────────────► │  python_engine       │
│  PHP 8.2 / Apache   │  /inspection/   │  FastAPI + pandas    │
│  Blade + Dompdf     │  plan           │  port 8000           │
└─────────┬───────────┘                 └──────────────────────┘
          │
          ▼
┌─────────────────────┐
│  db (MySQL 8.0)     │
│  host port 3307*    │
└─────────────────────┘
```

\* Host `3306` may already be in use; default compose maps MySQL to **3307**. Inside Docker, app always uses `db:3306`.

| Service | Image / build | Port |
|---------|---------------|------|
| `app` | `docker/php/Dockerfile` | http://localhost:8080 |
| `python_engine` | `docker/python/Dockerfile` | http://localhost:8000 |
| `db` | `mysql:8.0` | localhost:3307 → 3306 |

---

## Quick start

### Prerequisites

- Docker Desktop running
- Commands run from the repository root: `maintenance_app/`

### Start

```powershell
Copy-Item .env.example .env   # if needed
docker compose up -d --build
```

On first boot, the `app` entrypoint:

- installs Laravel 12 into `src/` if missing
- syncs DB settings into `src/.env`
- runs `php artisan key:generate` / `migrate`

### Verify

| URL | Purpose |
|-----|---------|
| http://localhost:8080/login | Login |
| http://localhost:8080/troubles | Trouble list (after login) |
| http://localhost:8000/health | Python health |
| http://localhost:8000/docs | FastAPI OpenAPI UI |

### Create a user (example)

```powershell
docker compose exec app php artisan tinker
```

```php
\App\Models\User::factory()->role(\App\Enums\UserRole::Leader, '運転Gr')->create([
    'email' => 'leader@example.com',
    'password' => 'password',
    'name' => 'Leader',
]);
```

Default password from the factory is `password` unless overridden.

---

## Screens & routes

All routes below (except `/login`) require `auth`.

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET/POST | `/login` | `login` | Sign in |
| POST | `/logout` | `logout` | Sign out |
| GET | `/troubles` | `troubles.index` | Trouble list |
| GET/POST | `/troubles/create`, `/troubles` | create/store | Register trouble |
| GET | `/troubles/{id}` | `troubles.show` | Detail + approve + PDF + repair link |
| GET | `/troubles/{id}/pdf` | `troubles.pdf` | Download 保全依頼表 PDF |
| POST | `/troubles/{id}/approve` | `troubles.approve` | Advance trouble workflow |
| GET/POST | `/troubles/{id}/repair-reports/...` | repair-reports.* | Create/store completion report |
| GET | `/repair-reports/{id}` | `repair-reports.show` | Report detail + approve |
| POST | `/repair-reports/{id}/approve` | `repair-reports.approve` | Advance repair workflow |
| GET | `/todos` | `todos.index` | Group TODO list (filterable) |
| GET | `/analytics` | `analytics.index` | Category aggregation |
| POST | `/analytics/plan` | `analytics.plan` | Call Python inspection planner |

---

## Database

### `troubles`

Field trouble records: categories (major / middle / minor), title, content, investigation, estimated cause, dates, spare parts, drawings, creating group, reporter, workflow `status`, and per-step approval flags/timestamps.

### `repair_reports`

Completion reports linked by `trouble_id`: categories, event details, repair date, used parts/drawings, trial run / operation records, repair workflow `status` and approvals.

### `todos`

Group/user tasks: `group_name`, `title`, `due_on`, `is_completed`, optional `trouble_id` / `user_id`. Created automatically when troubles (and workflow steps) are registered/advanced.

### `users`

Auth users with:

- `role` — see [Authorization](#authentication--authorization)
- `group_name` — e.g. `運転Gr`, `保全Gr`, `電気Gr`

---

## Workflows

### Trouble approval

```
Discoverer → Leader → Ops manager → Maintenance leader → Maintenance manager → Completed
```

Registration marks the discoverer step done and starts at **Leader**. Each approve sets the current step’s flag/`_approved_at`, moves `status`, updates TODOs, and sends notifications.

### Repair report approval

```
Maintenance staff → Leader → Manager → Ops leader → Ops manager → Completed
```

Created after field repair; staff step is marked on create, then leader waits. Concurrent double-approvals are blocked with row `lockForUpdate`.

---

## Authentication & authorization

- Unauthenticated access to protected URLs redirects to `/login`.
- Approvals use Laravel Policies:
  - **Role** must match the current workflow step (admins can do all).
  - **Group** must match the responsible group (ops group for ops steps; `保全Gr` for maintenance steps).
- Other-group leaders cannot approve another group’s trouble.

Roles (`App\Enums\UserRole`):

`admin`, `discoverer`, `leader`, `ops_manager`, `maintenance_staff`, `maintenance_leader`, `maintenance_manager`, `ops_leader`

---

## Notifications

On each workflow transition, TMQ can notify via:

- **Mail** (`MAIL_*`; local default often `log`)
- **Teams** Incoming Webhook (custom notification channel)

Configure in `src/.env` / `src/config/tmq.php`:

```env
TMQ_NOTIFICATIONS_ENABLED=true
TMQ_NOTIFY_DEFAULT_MAIL=tmq-notify@example.com
TMQ_NOTIFY_DEFAULT_TEAMS_WEBHOOK=https://outlook.office.com/webhook/...
```

Per-step overrides: `TMQ_NOTIFY_TROUBLE_*`, `TMQ_NOTIFY_REPAIR_*`.

---

## Python Engine

Located under `python/`:

| File | Role |
|------|------|
| `main.py` | FastAPI app (`/health`, `/analyze`, `/inspection/plan`) |
| `inspection_planner.py` | pandas logic: merge trouble aggregates + monitoring → recommended cycle/points |
| `data/monitoring_sample.csv` | Sample daily condition-monitoring data (used when request omits monitoring) |

Laravel calls the engine through `App\Services\PythonEngineClient` (`PYTHON_ENGINE_URL`, default `http://python_engine:8000`).

**Inspection plan idea:** higher trouble frequency / shorter recurrence / higher monitoring anomaly rate → higher risk score → shorter recommended inspection interval (bounded by min/max days). Suggested points come from category + hot monitoring points.

CLI alternative (stdin JSON → stdout JSON):

```powershell
docker compose exec -T python_engine python inspection_planner.py < payload.json
```

---

## Testing

PHPUnit Feature tests live in `src/tests/Feature/`:

| Class | Covers |
|-------|--------|
| `AuthenticationSecurityTest` | Guest redirect; forbidden cross-group approve |
| `WorkflowTransitionTest` | Ordered trouble & repair transitions |
| `ConcurrentMultiUserTest` | Multi-user register; serialized double approve |
| `PdfAndAnalyticsIntegrationTest` | PDF `%PDF` response; analytics; Python client (Http::fake) |

```powershell
docker compose exec app php artisan test --testsuite=Feature
```

Tests use SQLite in-memory (`phpunit.xml`). CSRF is disabled in the test `TestCase` base class.

---

## Directory structure

```
maintenance_app/
├── docker-compose.yml
├── .env / .env.example          # Compose ports & DB credentials
├── README.md
├── docker/
│   ├── php/                    # PHP 8.2 Apache Dockerfile, entrypoint, php.ini
│   └── python/                 # Python 3.10 Dockerfile, requirements.txt
├── python/                     # FastAPI + inspection planner + sample CSV
└── src/                        # Laravel application
    ├── app/
    │   ├── Enums/              # TroubleStatus, RepairReportStatus, UserRole
    │   ├── Http/Controllers/   # Auth, Trouble, RepairReport, Todo, Analytics
    │   ├── Models/
    │   ├── Policies/
    │   ├── Notifications/
    │   └── Services/           # Registration, workflow, PDF, analytics, Python client
    ├── database/migrations/
    ├── database/factories/
    ├── resources/views/        # Blade UI + PDF template
    ├── public/css|js/          # TMQ styles + speech-input.js
    ├── routes/web.php
    ├── config/tmq.php
    └── tests/Feature/
```

---

## Useful commands

```powershell
# Start / stop
docker compose up -d --build
docker compose down
docker compose down -v          # also wipe MySQL volume

# Logs
docker compose logs -f app
docker compose logs -f python_engine

# Laravel
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
docker compose exec app php artisan test --testsuite=Feature
docker compose exec app composer install

# Python
docker compose exec python_engine pip install -r /tmp/requirements.txt
# (requirements are baked at image build; rebuild python_engine after requirement changes)
```

### Notes

- First Laravel install can take several minutes (Composer inside the container).
- PDF Japanese text needs `fonts-ipafont-gothic` in the app image (included in `docker/php/Dockerfile`).
- Host MySQL port conflict: set `DB_PORT=3307` (or another free port) in the root `.env`.
