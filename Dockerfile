# Production-ish image for the demo deploy (Railway / Render / fly.io).
# Single container serving the API via `php artisan serve`.
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock artisan ./
COPY database/ database/
RUN composer install --prefer-dist --no-interaction --no-scripts

FROM php:8.4-cli-alpine

WORKDIR /var/www/html

# SQLite + Postgres drivers. El deploy elige el origen por DB_CONNECTION.
RUN apk add --no-cache sqlite-libs postgresql-libs \
    && apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS} sqlite-dev postgresql-dev \
    && docker-php-ext-install pdo_sqlite pdo_pgsql \
    && apk del .build-deps

COPY --from=vendor /app/vendor ./vendor
COPY . .

# Production env baseline; the deploy provider overrides what it needs.
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && touch database/database.sqlite

EXPOSE 8000

# Migrate + seed the demo dataset, then serve on the provider's port.
CMD ["sh", "-c", "php artisan migrate --force --seed && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
