#!/bin/bash
# Azure App Service startup script for Co-Auth
# Runs on every container boot before PHP-FPM takes over.

set -e

APP_DIR="/home/site/wwwroot"
cd "$APP_DIR"

echo "==> [startup] Running database migrations..."
php artisan migrate --force

echo "==> [startup] Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [startup] Creating storage symlink..."
php artisan storage:link 2>/dev/null || true

echo "==> [startup] Ensuring storage directories exist..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> [startup] Starting queue worker in background..."
nohup php artisan queue:work \
    --tries=3 \
    --timeout=90 \
    --sleep=3 \
    --max-jobs=500 \
    >> /home/LogFiles/queue-worker.log 2>&1 &

echo "==> [startup] Queue worker PID: $!"

echo "==> [startup] Starting Laravel Reverb WebSocket server on port 8080..."
nohup php artisan reverb:start \
    --host="0.0.0.0" \
    --port="8080" \
    >> /home/LogFiles/reverb.log 2>&1 &

echo "==> [startup] Reverb PID: $!"
echo "==> [startup] All background services started. Handing off to PHP-FPM..."
