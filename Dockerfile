FROM php:8.2-fpm

# --- Dépendances système ---
RUN apt-get update && apt-get install -y \
    git curl zip unzip nginx \
    libpng-dev libonig-dev libxml2-dev libzip-dev sqlite3 libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# --- Extensions PHP requises par Laravel ---
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# --- Composer ---
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# --- Copie du code ---
COPY . .

# --- Installation des dependances PHP (production) ---
RUN composer install --no-dev --optimize-autoloader --no-interaction

# --- Config Nginx et script de demarrage ---
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/start.sh /usr/local/bin/start.sh

# Nettoie BOM eventuel + fins de ligne Windows (CRLF -> LF)
RUN sed -i '1s/^\xEF\xBB\xBF//' /etc/nginx/sites-available/default \
    && sed -i 's/\r$//' /etc/nginx/sites-available/default \
    && sed -i '1s/^\xEF\xBB\xBF//' /usr/local/bin/start.sh \
    && sed -i 's/\r$//' /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# --- Permissions Laravel ---
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD ["/usr/local/bin/start.sh"]