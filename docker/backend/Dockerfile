FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    bash \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    openssl \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install apcu && docker-php-ext-enable apcu \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /backend

COPY composer.json composer.lock* ./
RUN composer install --no-scripts --no-autoloader --prefer-dist || true

COPY . .
RUN composer dump-autoload --optimize || true

RUN mkdir -p var/cache var/log config/jwt \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

EXPOSE 9000
CMD ["php-fpm"]
