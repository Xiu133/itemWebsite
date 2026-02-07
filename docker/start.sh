#!/bin/bash

# 產生快取
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 執行資料庫遷移
php artisan migrate --force

# 啟動 Supervisor（管理 nginx + php-fpm）
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
