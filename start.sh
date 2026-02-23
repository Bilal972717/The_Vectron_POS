#!/bin/bash

# Wait a few seconds for the database to be ready
sleep 10

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache

# Start PHP-FPM
php-fpm