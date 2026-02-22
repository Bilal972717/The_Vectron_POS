# Use PHP CLI for Laravel built-in server
FROM php:8.2-cli

WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Expose port (optional)
EXPOSE 8080

# Enable error reporting (for debugging)
RUN echo "display_errors=On\nerror_reporting=E_ALL" > /usr/local/etc/php/conf.d/docker-php-errors.ini

# Start Laravel built-in server on Render's $PORT
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]