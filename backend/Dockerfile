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

COPY backend/composer.json ./
RUN composer install --no-scripts --no-autoloader --no-interaction --no-dev

COPY backend/ .

RUN composer dump-autoload --optimize

RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

EXPOSE 8000

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT"]
