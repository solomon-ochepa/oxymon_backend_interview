# Loan App — PHP 8.5 / Laravel 13

# ─── Stage 1: system deps + PHP extensions ───────────────────────────────────
# Rebuilt only when the base image or extension list changes.
FROM php:8.5-cli AS base

# System libraries needed by the PHP extensions below
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libonig-dev \
        libzip-dev \
        libicu-dev \
        libsqlite3-dev \
    && docker-php-ext-install -j"$(nproc)" \
        mbstring \
        pdo_sqlite \
        pdo_mysql \
        bcmath \
        zip \
        intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer (copied from the official Composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ─── Stage 2: Composer vendor install ────────────────────────────────────────
# Rebuilt only when composer.json / composer.lock change.
FROM base AS vendor

WORKDIR /app

# Install PHP dependencies first so this layer is cached unless deps change
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist

# ─── Stage 3: final runtime image ────────────────────────────────────────────
# Copies only the built artefacts; no dev tools, no build-time layer noise.
FROM base AS app

WORKDIR /app

COPY --from=vendor /app/vendor ./vendor
# Copy the rest of the application
COPY . .

RUN composer dump-autoload --optimize \
    && chmod +x docker/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
