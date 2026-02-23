#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for database..."
sleep 10

# Run migrations safely
php artisan migrate --force

# Clear and cache config
php artisan config:cache

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000