#!/bin/sh
set -e

echo "Generating APP_KEY if needed..."
php artisan key:generate --force 2>/dev/null || true

echo "Running migrations..."
php artisan migrate --force 2>/dev/null || echo "Migrations failed but continuing..."

echo "Optimizing framework caches..."
rm -f public/hot
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true
php artisan event:cache 2>/dev/null || true

echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/frankenphp/Caddyfile