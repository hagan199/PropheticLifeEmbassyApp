#!/bin/sh
set -e

echo "Starting production entrypoint..."

# Wait for database with proper health check
echo "Waiting for PostgreSQL..."
until PGPASSWORD=$DB_PASSWORD psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c '\q' 2>/dev/null; do
  echo "PostgreSQL is unavailable - sleeping"
  sleep 2
done

echo "PostgreSQL is ready!"

# Wait for Redis
echo "Waiting for Redis..."
until redis-cli -h "$REDIS_HOST" -a "$REDIS_PASSWORD" ping 2>/dev/null | grep -q PONG; do
  echo "Redis is unavailable - sleeping"
  sleep 2
done

echo "Redis is ready!"

# Run migrations with lock to prevent concurrent runs
echo "Running database migrations..."
php artisan migrate --force --isolated

# Seed only if RUN_SEEDER environment variable is set
if [ "$RUN_SEEDER" = "true" ]; then
    echo "Running database seeder..."
    php artisan db:seed --force
fi

# Cache optimization for production
echo "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if not exists
if [ ! -L public/storage ]; then
    php artisan storage:link
fi

# Ensure proper permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Production setup complete!"

# Start PHP-FPM
exec php-fpm
