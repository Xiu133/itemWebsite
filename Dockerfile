# Stage 1: 安裝 Composer 依賴（取得 Ziggy 等套件）
FROM composer:latest AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

# Stage 2: 編譯前端資源
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install
COPY . .
COPY --from=composer /app/vendor ./vendor
RUN npm run build

# Stage 2: PHP + Nginx
FROM php:8.3-fpm

WORKDIR /var/www/html

# 安裝系統依賴 + Nginx + Supervisor
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 複製專案檔案
COPY . /var/www/html

# 從 Stage 1 複製編譯好的前端資源
COPY --from=frontend /app/public/build /var/www/html/public/build

# 安裝 PHP 依賴（生產模式）
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Nginx 設定（Cloud Run 使用 PORT 環境變數，預設 8080）
COPY docker/nginx-cloudrun.conf /etc/nginx/sites-available/default

# Supervisor 設定（同時管理 nginx + php-fpm）
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP 優化設定
RUN echo "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.max_accelerated_files=10000\n\
opcache.validate_timestamps=0" > /usr/local/etc/php/conf.d/opcache.ini

# 建立 storage 目錄結構（因 .gcloudignore 排除了 storage/）
RUN mkdir -p /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/testing \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

# 設定權限
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 啟動腳本
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
