FROM php:8.2-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-interaction --optimize-autoloader

# Fix permissions
RUN chmod -R 775 storage bootstrap/cache

# Clear config cache
RUN php artisan config:clear
RUN php artisan cache:clear

# Run migrations
RUN php artisan migrate --force

# Expose port for Railway
EXPOSE 8080

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT}"]