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

COPY opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --no-scripts

EXPOSE 8000

CMD ["sh", "-c", "\
  if [ ! -f .env ]; then \
    echo APP_KEY= > .env && \
    echo APP_ENV=local >> .env && \
    echo APP_DEBUG=true >> .env && \
    echo APP_URL=http://localhost:8000 >> .env && \
    echo DB_CONNECTION=mysql >> .env && \
    echo DB_HOST=$DB_HOST >> .env && \
    echo DB_PORT=$DB_PORT >> .env && \
    echo DB_DATABASE=$DB_NAME >> .env && \
    echo DB_USERNAME=$DB_USER >> .env && \
    echo DB_PASSWORD=$DB_PASSWORD >> .env && \
    echo SESSION_DRIVER=cookie >> .env && \
    echo CACHE_STORE=file >> .env && \
    echo QUEUE_CONNECTION=sync >> .env; \
  fi && \
  php artisan key:generate --force && \
  php artisan config:clear && \
  php artisan migrate --force; \
  php artisan serve --host=0.0.0.0 --port=$PORT"]
