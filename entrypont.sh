#!/bin/bash
set -e

echo "Generating APP_KEY if needed..."
php artisan key:generate --force 2>/dev/null || true

echo "Running migrations..."
php artisan migrate --force || true

echo "Clearing caches..."
php artisan cache:clear || true
php artisan config:cache || true

echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/frankenphp/Caddyfile