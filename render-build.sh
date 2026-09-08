#!/usr/bin/env bash

# Exit on error
set -o errexit

echo "==> Downloading Composer..."
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid composer installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet
rm composer-setup.php
# composer.phar sekarang ada di direktori saat ini

echo "==> Installing PHP dependencies..."
php composer.phar install --no-dev --optimize-autoloader

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
