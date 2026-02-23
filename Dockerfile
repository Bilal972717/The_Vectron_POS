FROM php:8.2-fpm

WORKDIR /var/www/html

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Permissions
RUN chmod -R 775 storage bootstrap/cache

# Clear caches
RUN php artisan config:clear
RUN php artisan cache:clear

# Copy entrypoint
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8000

CMD ["/start.sh"]