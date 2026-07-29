FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    zip \
    bash \
    ca-certificates \
    && docker-php-ext-install pdo_mysql zip opcache \
    && update-ca-certificates

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY backend/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

WORKDIR /var/www/html

COPY backend/composer.json backend/composer.lock ./

RUN composer install --no-dev --no-interaction

COPY backend/ .

EXPOSE 8000

CMD ["sh", "-c", "cp -n .env.example .env 2>/dev/null; php artisan key:generate --force; php artisan migrate --force; php artisan serve --host=0.0.0.0 --port=$PORT"]
