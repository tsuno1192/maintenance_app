# hoikuku-app

保育施設向けの業務支援 Web アプリケーションです。  
保護者・保育士・管理者・相談員のロールに応じて、シフト管理、支援記録、相談・問い合わせ、ドキュメンテーションなどを提供します。

## 技術スタック

| 項目 | 内容 |
|------|------|
| フレームワーク | Laravel 13 / PHP 8.3 |
| 認証 | Laravel Breeze（Blade） |
| フロント | Blade / Alpine.js / Tailwind CSS / Vite |
| API 認証 | Laravel Sanctum |
| AI | OpenAI（`openai-php/laravel`） |
| DB（ローカル既定） | SQLite |
| DB（Docker） | MySQL 8 |

## ロール

| ロール | 説明 |
|--------|------|
| `admin` | 管理者 |
| `staff` | 保育士・スタッフ |
| `parent` | 保護者 |
| `counselor` | 相談員 |

権限は `EnsureUserHasRole` ミドルウェアで制御します。

## 主な機能

### シフト管理（管理者・スタッフ）

- シフトマトリックスの表示
- 希望休・出勤希望・時間指定を考慮した自動最適化
- スキル要件・公平性・連続勤務上限（6日）を考慮

### 支援ダッシュボード（スタッフ・管理者・保護者）

- 児童一覧・個別支援計画の閲覧
- 日々の支援ログ（申し送り）の記録（スタッフのみ）
- 保護者は自分の児童のみ閲覧可能

### 相談・問い合わせ（保護者 ↔ 施設）

- 保護者からの相談チケット送信
- AI によるメッセージのマイルド化（`AITextTransformer`）
- 問い合わせへの AI 一次回答・言い換え・感情フラグ記録
- スタッフ／相談員向け相談受信ボックス

### ドキュメンテーション

- スタッフが児童写真をアップロード
- GPT Vision でモンテッソーリ／レッジョ・エミリアの観点に基づく保護者向け文章を生成
- 写真はローカルの非公開ディスクに保存

### 認証・プロフィール

- ログイン / 登録 / パスワード再設定 / メール確認
- プロフィール編集・削除

## セットアップ

### 1. 依存関係のインストール

```bash
composer install
npm install
```

### 2. 環境変数

```bash
cp .env.example .env
php artisan key:generate
```

必要に応じて `.env` を編集してください。

| 変数 | 用途 |
|------|------|
| `OPENAI_API_KEY` | OpenAI API キー（相談マイルド化・問い合わせ・ドキュメンテーションで使用） |
| `DB_*` | データベース接続（既定は SQLite） |

### 3. マイグレーション

```bash
php artisan migrate
```

### 4. フロントエンドビルド

```bash
npm run build
# 開発時は
npm run dev
```

### 5. 起動

```bash
php artisan serve
```

または Composer の開発スクリプト:

```bash
composer run dev
```

（サーバー / キュー / ログ / Vite を同時起動）

## Docker

```bash
docker compose up -d --build
```

| サービス | 内容 |
|----------|------|
| `app` | Apache、ポート `8080` |
| `db` | MySQL 8、ポート `3306` |

アプリ: http://localhost:8080

## 主な画面・ルート

| 画面 | ルート名 | 対象ロール |
|------|----------|------------|
| ダッシュボード | `dashboard` | 認証ユーザー |
| シフトマトリックス | `admin.shifts.matrix` | staff / admin |
| シフト自動最適化 | `admin.shifts.optimize` | staff / admin |
| 相談受信ボックス | `admin.consultations.index` | staff / admin / counselor |
| 支援ダッシュボード | `support.index` | staff / admin / parent |
| ドキュメンテーション | `documentations.index` | staff / admin / parent / counselor |
| 相談・問合せ作成 | `consultations.create` | parent |

## API（Sanctum）

| メソッド | パス | 内容 |
|----------|------|------|
| POST | `/api/shifts/optimize` | シフト最適化（staff / admin） |
| GET/POST | `/api/consultations` | 相談一覧・作成 |
| POST | `/api/consultations/{id}/messages` | メッセージ送信 |
| PATCH | `/api/consultations/{id}/status` | ステータス更新 |

## ディレクトリの目安

```
app/
  Http/Controllers/   # Web・API・Admin コントローラ
  Models/             # User, Child, Shift, Documentation など
  Services/           # ShiftOptimizerService, AITextTransformer
  Policies/           # DocumentationPolicy など
resources/views/      # Blade テンプレート
routes/web.php        # Web ルート
routes/api.php        # API ルート
database/migrations/  # DB スキーマ
```

## ライセンス

MIT
