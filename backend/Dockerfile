FROM php:8.3-cli-alpine

# Instalar dependencias del sistema y extensiones de PHP requeridas
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    zip \
    bash \
    && docker-php-ext-install pdo_mysql zip opcache

# Instalar Composer desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar configuración optimizada de OPcache
COPY opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

WORKDIR /var/www/html

# Copiar archivos de dependencias e instalarlos
COPY composer.json ./
RUN composer install --no-scripts --no-autoloader --no-interaction

# Copiar el código de la aplicación
COPY . .

# Generar autoload optimizado
RUN composer dump-autoload --optimize

# Exponer el puerto del servidor de desarrollo de Laravel
EXPOSE 8000

# Levantar el backend de Laravel en 0.0.0.0 para acceso Docker
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
