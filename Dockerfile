# ====================================================
# STAGE 1: Build Frontend Vue & Vendor (Wayfinder Support)
# ====================================================
FROM node:20-alpine AS frontend-builder

# 1. Install PHP & tools standar Alpine
RUN apk add --no-cache \
    php \
    php-cli \
    php-phar \
    php-mbstring \
    php-openssl \
    php-xml \
    php-dom \
    php-tokenizer \
    php-fileinfo \
    php-session \
    php-ctype \
    php-json \
    php-curl \
    git curl zip unzip

# 2. Copy Composer executable dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 3. Copy seluruh source code project
COPY . .

# 4. Install Composer dengan mengabaikan kebutuhan ekstensi runtime di STAGE 1
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 5. Install dependensi NPM & Build Vue/Vite
RUN npm install
ENV NODE_OPTIONS="--max-old-space-size=2048"
RUN npm run build

# ====================================================
# STAGE 2: PHP-FPM + SQLite Runtime
# ====================================================
FROM php:8.4-fpm-alpine

# 1. Install dependensi sistem & SQLite3
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    libonig-dev \
    sqlite3 \
    && docker-php-ext-install pdo_sqlite mbstring zip bcmath \
    && apt-get purge -y --auto-remove libzip-dev libsqlite3-dev libonig-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# 2. Install ekstensi PHP untuk SQLite & Laravel
RUN docker-php-ext-install pdo_sqlite mbstring zip bcmath opcache

# 3. Set Working Directory
WORKDIR /var/www

# 4. Copy source code aplikasi
COPY . /var/www

# 5. Copy folder 'vendor' dan 'public/build' dari STAGE 1
COPY --from=frontend-builder /app/vendor /var/www/vendor
COPY --from=frontend-builder /app/public/build /var/www/public/build

# 6. Buat file SQLite & Atur Permission Folder
RUN mkdir -p /var/www/database \
    && touch /var/www/database/database.sqlite \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/database

EXPOSE 9000
CMD ["php-fpm"]