#!/bin/sh
set -e

APP_DIR=/var/www/html
DB_DATABASE=${DB_DATABASE:-$APP_DIR/database/database.sqlite}

# .env
if [ ! -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    php "$APP_DIR/artisan" key:generate --force --no-interaction
fi

# SQLite database
if [ ! -f "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/database"

php "$APP_DIR/artisan" package:discover --ansi
php "$APP_DIR/artisan" migrate --force --no-interaction

if [ "$SEED_DB" = "true" ] && [ ! -f "$(dirname "$DB_DATABASE")/.seeded" ]; then
    php "$APP_DIR/artisan" db:seed --force --no-interaction
    touch "$(dirname "$DB_DATABASE")/.seeded"
fi

# Bootstrap admin pertama (produksi tanpa SEED_DB): hanya saat tabel users kosong
USER_COUNT=$(php "$APP_DIR/artisan" tinker --execute="echo App\\Models\\User::count();" 2>/dev/null | tail -1)
if [ "${ADMIN_PASSWORD:-}" != "" ] && [ "$USER_COUNT" = "0" ]; then
    ADMIN_USERNAME=${ADMIN_USERNAME:-admin}
    ADMIN_NAME=${ADMIN_NAME:-"Admin Kaldera"}
    ADMIN_EMAIL=${ADMIN_EMAIL:-admin@kaldera.id}
    php "$APP_DIR/artisan" tinker --execute="
        App\\Models\\User::create([
            'name' => '$ADMIN_NAME',
            'username' => '$ADMIN_USERNAME',
            'email' => '$ADMIN_EMAIL',
            'password' => '$ADMIN_PASSWORD',
            'is_admin' => true,
        ]);
        echo 'admin-created';
    " 2>/dev/null | tail -1
fi

exec "$@"