FROM php:8.2-cli

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy app
COPY . .

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader

# Fix permissions (VERY IMPORTANT for Laravel)
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

# Start Laravel server on Railway PORT
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT}"]