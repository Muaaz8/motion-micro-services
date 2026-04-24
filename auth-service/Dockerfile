FROM php:8.3-alpine

RUN apk add --no-cache curl zip unzip git libzip-dev oniguruma-dev

RUN docker-php-ext-install pdo pdo_mysql mbstring bcmath zip pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"]
