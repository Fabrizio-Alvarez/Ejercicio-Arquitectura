# Production-ish image for the demo deploy (Railway / Render / fly.io).
# Single container serving the API via `php artisan serve`.
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock artisan ./
COPY database/ database/
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts

FROM php:8.4-cli-alpine

WORKDIR /var/www/html

# pdo_sqlite for the SQLite storage.
RUN apk add --no-cache sqlite-libs \
    && docker-php-ext-install pdo_sqlite

COPY --from=vendor /app/vendor ./vendor
COPY . .

# Production env baseline; the deploy provider overrides what it needs.
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && touch database/database.sqlite

EXPOSE 8000

# Run migrations then serve on the port the provider injects (default 8000).
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
