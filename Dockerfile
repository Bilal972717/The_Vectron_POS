# Stage 0: PHP + Composer
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Clear caches
RUN php artisan config:clear
RUN php artisan cache:clear

# Copy the entrypoint script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Expose port (optional)
EXPOSE 8000

# Start the container using the script
CMD ["/start.sh"]