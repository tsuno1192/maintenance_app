#!/bin/bash
set -e

cd /var/www/html

# Auto-install Laravel if project is missing
if [ ! -f "artisan" ]; then
  echo "[TMQ] Laravel not found. Installing laravel/laravel ..."

  # composer create-project requires an empty directory
  # Remove placeholders such as .gitkeep / lost+found
  find . -mindepth 1 -maxdepth 1 -exec rm -rf {} +

  # Laravel 13+ needs PHP 8.3; pin to 12.x for PHP 8.2
  COMPOSER_MEMORY_LIMIT=-1 composer create-project laravel/laravel:^12.0 . --prefer-dist --no-interaction

  echo "[TMQ] Laravel installed successfully."
fi

# Ensure .env exists
if [ ! -f ".env" ]; then
  if [ -f ".env.example" ]; then
    cp .env.example .env
    echo "[TMQ] Created .env from .env.example"
  fi
fi

# Sync DB / app settings from container environment
if [ -f ".env" ]; then
  php -r '
    $envFile = ".env";
    $vars = [
      "APP_NAME" => getenv("APP_NAME") ?: "TMQ",
      "APP_ENV" => getenv("APP_ENV") ?: "local",
      "APP_DEBUG" => getenv("APP_DEBUG") ?: "true",
      "APP_URL" => getenv("APP_URL") ?: "http://localhost:8080",
      "DB_CONNECTION" => "mysql",
      "DB_HOST" => getenv("DB_HOST") ?: "db",
      "DB_PORT" => "3306",
      "DB_DATABASE" => getenv("DB_DATABASE") ?: "tmq",
      "DB_USERNAME" => getenv("DB_USERNAME") ?: "tmq",
      "DB_PASSWORD" => getenv("DB_PASSWORD") ?: "secret",
    ];
    $content = file_get_contents($envFile);
    foreach ($vars as $key => $value) {
      $value = str_replace(["\\", "\""], ["\\\\", "\\\""], $value);
      if (preg_match("/^{$key}=.*/m", $content)) {
        $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
      } else {
        $content .= PHP_EOL . "{$key}=\"{$value}\"";
      }
    }
    $py = getenv("PYTHON_ENGINE_URL") ?: "http://python_engine:8000";
    if (strpos($content, "PYTHON_ENGINE_URL=") === false) {
      $content .= PHP_EOL . "PYTHON_ENGINE_URL=\"{$py}\"" . PHP_EOL;
    } else {
      $content = preg_replace("/^PYTHON_ENGINE_URL=.*/m", "PYTHON_ENGINE_URL=\"{$py}\"", $content);
    }
    file_put_contents($envFile, $content);
  '
fi

# Generate APP_KEY if missing
if [ -f ".env" ] && ! grep -qE '^APP_KEY=base64:' .env; then
  php artisan key:generate --force || true
fi

# Permissions for storage / cache
if [ -d "storage" ] && [ -d "bootstrap/cache" ]; then
  chown -R www-data:www-data storage bootstrap/cache || true
  chmod -R ug+rwx storage bootstrap/cache || true
fi

# Wait for MySQL then migrate (optional, non-fatal)
if [ -f "artisan" ]; then
  echo "[TMQ] Waiting for database..."
  for i in $(seq 1 30); do
    if php -r '
      try {
        new PDO(
          sprintf("mysql:host=%s;port=%s;dbname=%s", getenv("DB_HOST") ?: "db", "3306", getenv("DB_DATABASE") ?: "tmq"),
          getenv("DB_USERNAME") ?: "tmq",
          getenv("DB_PASSWORD") ?: "secret"
        );
        exit(0);
      } catch (Throwable $e) {
        exit(1);
      }
    '; then
      echo "[TMQ] Database is ready."
      php artisan migrate --force || echo "[TMQ] migrate skipped or failed (will retry later)."
      break
    fi
    echo "[TMQ] DB not ready yet ($i/30)..."
    sleep 2
  done
fi

echo "[TMQ] Starting Apache..."
exec "$@"
