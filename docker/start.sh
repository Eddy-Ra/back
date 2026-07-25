#!/bin/sh
set -e

PORT="${PORT:-10000}"

# Remplace le placeholder de port par le port réel fourni par Render
sed -i "s/__PORT__/$PORT/g" /etc/nginx/sites-available/default

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

php-fpm -D
nginx -g "daemon off;"