#!/bin/sh
set -e

echo "Waiting for database..."
sleep 10

# Clear config cache
php artisan config:clear
php artisan cache:clear || true

# Run migrations
php artisan migrate --force || true

# Seed database if needed
php artisan db:seed --force || true

# Start server
exec php artisan serve --host=0.0.0.0 --port=8000
