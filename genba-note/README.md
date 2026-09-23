# 現場ノート（genba-note）

工場メンテナンス現場向けのオールインワン管理アプリです。  
設備の状態把握、申し送り（現場メモ）、工具の貸出管理を一つの画面から扱える「完成形の土台」として構築しています。

## 概要

現場作業員・管理者がスマホ／タブレットでも扱いやすい UI を前提に、次の業務をカバーします。

| 機能 | 内容 |
| --- | --- |
| 認証・権限 | Laravel Breeze（Blade）によるログイン／登録／プロフィール。ロールは `admin` / `worker` |
| 設備管理 | QR 識別子付き設備マスタ。稼働中／停止／保全のステータス管理 |
| 申し送り | 設備単位の申し送り投稿。タグ・画像添付・対応ステータス |
| 工具管理 | 工具マスタと貸出／返却履歴 |
| API | Machine / Memo / Tool の REST API（将来の SPA・モバイル連携用） |

## 技術スタック

- PHP 8.3 + Laravel 13
- 認証: Laravel Breeze（Blade スタック）+ Laravel Sanctum（API）
- DB: MySQL（主キーは UUID）
- フロント: Blade + Tailwind CSS + Alpine.js
- ストレージ: ローカルディスク（`MEDIA_DISK` で S3 へ切替可能な設計）

## 主なデータモデル

全テーブルで UUID をプライマリキーに採用しています。

- `users` … 氏名・メール・パスワード・ロール（admin/worker）
- `machines` … 設備名・`qr_identifier`・マニュアル URL・場所・稼働ステータス
- `memos` … 設備／投稿者・本文・タグ（JSON）・対応ステータス
- `memo_images` … 申し送り画像のパス（実体は Storage）
- `tools` … 工具名・シリアル・ステータス・現在位置
- `tool_logs` … 貸出／返却アクションと備考

ステータス類は Enum で定義しています（例: `MachineStatus`, `MemoStatus`, `ToolStatus`, `UserRole`）。

## ディレクトリ構成（抜粋）

```
app/
  Enums/                 # ロール・各種ステータス
  Http/
    Controllers/
      Auth/              # Breeze 認証
      Web/               # Blade 用（Dashboard / Machine / Memo / Tool）
      Api/               # REST API 用
    Requests/            # FormRequest
    Resources/           # API Resource
    Middleware/          # role ミドルウェア
  Models/
  Services/              # 画像最適化・AIタグ付け（骨組み）
  View/Components/       # AppLayout / GuestLayout など
database/
  migrations/
  factories/
  seeders/
resources/views/
  layouts/               # app / guest / navigation
  machines|memos|tools/  # 一覧・詳細・作成・編集
  auth/ profile/
routes/
  web.php auth.php api.php
```

## 画面・UX の方針

- ダークモード対応
- 手袋操作を想定した大きめのタップ領域・文字サイズ
- ステータスはバッジで視覚表示
- どの画面からでも申し送り投稿へ行けるフローティングアクションボタン（FAB）

## セットアップ

### 前提

- PHP 8.3+
- Composer
- Node.js / npm
- MySQL

### 手順

```bash
composer install
cp .env.example .env
php artisan key:generate

# .env の DB_* を環境に合わせて編集
php artisan migrate --seed
php artisan storage:link

npm install
npm run build

php artisan serve
```

開発時フロントのホットリロード:

```bash
composer run dev
# または npm run dev と php artisan serve を別ターミナルで起動
```

### テスト用アカウント（Seeder）

| ロール | メール | パスワード |
| --- | --- | --- |
| 管理者 | `admin@genba-note.test` | `password` |
| 作業員 | `worker@genba-note.test` | `password` |

## 主な URL

| パス | 説明 |
| --- | --- |
| `/login` | ログイン |
| `/dashboard` | ダッシュボード（設備・工具サマリ、未対応申し送り） |
| `/machines` | 設備一覧 |
| `/memos` | 申し送り一覧 |
| `/memos/create` | 申し送り投稿 |
| `/tools` | 工具一覧 |
| `/profile` | プロフィール |
| `/api/*` | REST API（Sanctum 認証） |

## 設計メモ

- **FormRequest** で入力検証（QR 形式、画像 MIME／枚数／サイズなど）
- **ImageOptimizationService** … 申し送り画像の保存。リサイズ最適化は拡張ポイント
- **AiTaggingService** … 本文からの自動タグ付け用の呼び出し口（将来 OpenAI 等を接続）
- **MEDIA_DISK** … `.env` で `public` → `s3` に切り替えてクラウド移行可能
- **role ミドルウェア** … `->middleware('role:admin')` のように利用可能

## ライセンス

このアプリケーションはプロジェクト固有の実装です。  
フレームワーク本体（Laravel）は [MIT license](https://opensource.org/licenses/MIT) です。
