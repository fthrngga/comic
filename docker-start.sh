#!/bin/bash
set -e

echo "==> Caching Laravel config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Creating storage symlink..."
php artisan storage:link || true

echo "==> Starting Apache..."
exec apache2-foreground
