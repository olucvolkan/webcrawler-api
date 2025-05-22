#!/bin/sh
set -e

echo "Checking directory structure and permissions..."
ls -la /app
ls -la /app/public

echo "Checking PHP-FPM configuration..."
php-fpm -t

echo "Checking nginx configuration..."
nginx -t

# Wait for database to be ready
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Waiting for database to be ready..."
  sleep 2
done

# Run migrations if needed
php bin/console doctrine:migrations:migrate --no-interaction

# Ensure proper permissions
chown -R www-data:www-data /app/var
chmod -R 777 /app/var
chown -R www-data:www-data /app/public
chmod -R 777 /app/public

exec "$@" 