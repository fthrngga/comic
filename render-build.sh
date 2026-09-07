#!/usr/bin/env bash

# Exit on error
set -o errexit

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "==> Installing Node.js dependencies..."
npm ci

echo "==> Building frontend assets..."
npm run build

echo "==> Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Creating storage symlink..."
php artisan storage:link

echo "==> Build complete!"
