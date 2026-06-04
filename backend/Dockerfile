FROM php:8.2-fpm

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
    && chmod -R 777 var

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
