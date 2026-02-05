FROM php:8.3-fpm

# 設定工作目錄
WORKDIR /var/www/html

# 安裝系統依賴
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
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 建立使用者
RUN groupadd -g 1000 www && useradd -u 1000 -ms /bin/bash -g www www

# 複製專案檔案
COPY --chown=www:www . /var/www/html

# 切換使用者
USER www

EXPOSE 9000
CMD ["php-fpm"]
