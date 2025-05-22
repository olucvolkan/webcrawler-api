#!/bin/sh
set -e

# Wait for database to be ready
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Waiting for database to be ready..."
  sleep 2
done

# Ensure proper permissions for worker
chown -R www-data:www-data /app/var
chmod -R 777 /app/var

exec "$@" 