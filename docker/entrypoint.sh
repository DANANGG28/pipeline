#!/bin/sh
set -e

# .env
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    php /var/www/html/artisan key:generate --force --no-interaction
fi

# SQLite database
if [ ! -f "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

php /var/www/html/artisan package:discover --ansi
php /var/www/html/artisan migrate --force --no-interaction

if [ "$SEED_DB" = "true" ] && [ ! -f "$(dirname "$DB_DATABASE")/.seeded" ]; then
    php /var/www/html/artisan db:seed --force --no-interaction
    touch "$(dirname "$DB_DATABASE")/.seeded"
fi

exec "$@"
