#!/bin/sh
set -e

PORT="${PORT:-10000}"

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

php-fpm -D
nginx -g "daemon off;"