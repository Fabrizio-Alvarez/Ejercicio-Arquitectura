# Production-ish image for the demo deploy (Fly.io / Railway / Render).
# Single container: PHP API + Inertia/Vue frontend (Vite-compiled).

# ---- Stage 1: PHP dependencies (composer) ----
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock artisan ./
COPY database/ database/
RUN composer install --prefer-dist --no-interaction --no-scripts

# ---- Stage 2: Frontend assets (Vite / Vue / Tailwind) ----
FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
COPY resources/ resources/
COPY public/ public/
RUN npm ci --ignore-scripts && npm run build

# ---- Stage 3: Runtime ----
FROM php:8.4-cli-alpine

WORKDIR /var/www/html

# SQLite + Postgres drivers. El deploy elige el origen por DB_CONNECTION.
RUN apk add --no-cache sqlite-libs postgresql-libs \
    && apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS} sqlite-dev postgresql-dev \
    && docker-php-ext-install pdo_sqlite pdo_pgsql \
    && apk del .build-deps

COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY . .

# Production env baseline; the deploy provider overrides what it needs.
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && touch database/database.sqlite

EXPOSE 8000

# Ensure storage structure exists (Fly volume mount starts empty), then
# migrate + seed the demo dataset, and serve on the provider's port.
CMD ["sh", "-c", "mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/supermercado bootstrap/cache && php artisan migrate --force --seed && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
