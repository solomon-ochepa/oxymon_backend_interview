#!/usr/bin/env bash
set -e

# Ensure an application key exists
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi

# Ensure the SQLite database file exists and is writable
mkdir -p database
touch database/database.sqlite

# Apply migrations (idempotent) and regenerate the Swagger docs
php artisan migrate --force
php artisan l5-swagger:generate

exec "$@"
