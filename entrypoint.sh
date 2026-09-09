#!/bin/bash

echo "Generating APP_KEY if needed..."
php artisan key:generate --force 2>/dev/null || true

echo "Running migrations (non-blocking)..."
php artisan migrate --force 2>/dev/null || echo "Migrations failed but continuing..."

echo "Clearing caches (non-blocking)..."
php artisan cache:clear 2>/dev/null || true
php artisan config:cache 2>/dev/null || true

echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/frankenphp/Caddyfile