# ---- Stage 1: Composer dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist

# ---- Stage 2: Frontend assets ----
FROM node:24-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# ---- Stage 3: Runtime ----
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

RUN docker-php-ext-install pdo_sqlite opcache \
    && docker-php-ext-enable opcache \
    && apk add --no-cache nginx supervisor

COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache database \
    && mkdir -p /run/nginx /var/log/supervisor

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/supervisord.conf /etc/supervisord.conf
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
