# Use official PHP CLI image instead of FPM
FROM php:8.2-cli

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application code
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Expose the port Railway will use
EXPOSE 8080

# Start PHP built-in server on Railway's $PORT
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t public"]