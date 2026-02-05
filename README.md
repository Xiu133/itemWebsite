# Laravel E-Commerce Platform

一個功能完整的電商平台，基於 Laravel 12 + Vue.js 3 + Inertia.js 建構，整合台灣綠界金流與物流系統。

## 目錄

- [功能特色](#功能特色)
- [技術架構](#技術架構)
- [系統需求](#系統需求)
- [安裝指南](#安裝指南)
- [環境設定](#環境設定)
- [資料庫架構](#資料庫架構)
- [API 文件](#api-文件)
- [專案結構](#專案結構)
- [開發指令](#開發指令)
- [部署](#部署)

---

## 功能特色

### 商品管理
- 多層級分類系統（支援父子分類）
- 品牌管理
- 標籤系統（多對多關聯）
- 商品上下架控制
- 軟刪除支援
- 庫存即時追蹤

### 購物車系統
- 雙模式支援：訪客（Session）/ 會員（User）
- 登入後自動合併購物車
- 價格快照機制
- 即時庫存驗證

### 訂單管理
- 完整訂單流程（待付款 → 已付款 → 處理中 → 已出貨 → 已完成）
- 訂單取消與庫存自動回補
- 訂單編號自動生成
- 商品快照保存

### 金流整合（綠界 ECPay）
- 信用卡付款
- 貨到付款

### 物流整合（綠界 ECPay）
- 宅配：黑貓宅急便、宅配通
- 物流狀態追蹤
- 出貨通知 Webhook

### 商家後台
- 獨立商家認證系統
- 商品 CRUD 管理
- 訂單管理
- 付款狀態追蹤
- 物流管理
- 庫存調整

### 其他功能
- 全文搜尋
- 活動日誌（Audit Trail）
- 雙因素認證（2FA）
- Email 驗證

---

## 技術架構

### 後端

| 套件 | 版本 | 用途 |
|------|------|------|
| Laravel | 12.0 | Web 框架 |
| PHP | 8.2+ | 程式語言 |
| PostgreSQL | - | 資料庫 |
| Laravel Sanctum | 4.0 | API 認證 |
| Laravel Jetstream | 5.4 | 使用者認證 |
| Inertia.js | 2.0 | SPA 橋接 |
| Spatie Activity Log | 4.11 | 活動日誌 |
| ECPay SDK | 1.3 | 金流/物流 |

### 前端

| 套件 | 版本 | 用途 |
|------|------|------|
| Vue.js | 3.3 | 前端框架 |
| Tailwind CSS | 3.4 | CSS 框架 |
| Vite | 7.0 | 建構工具 |
| Axios | 1.11 | HTTP 請求 |

### 資料庫特色

- **Stored Procedures**：訂單建立、訂單取消
- **Triggers**：庫存自動扣減與回補
- **Row-Level Locking**：防止超賣與 Race Condition

---

## 系統需求

- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18.0
- PostgreSQL >= 14.0（或 MySQL >= 8.0）
- Redis（選用，用於快取）

---

## 安裝指南

### 方法一：使用 Composer Script（推薦）

```bash
# Clone 專案
git clone <repository-url> itemWebsite
cd itemWebsite

# 執行完整安裝
composer setup
```

### 方法二：手動安裝

```bash
# 1. Clone 專案
git clone <repository-url> itemWebsite
cd itemWebsite

# 2. 安裝 PHP 依賴
composer install

# 3. 安裝前端依賴
npm install

# 4. 複製環境設定檔
cp .env.example .env

# 5. 產生應用程式金鑰
php artisan key:generate

# 6. 設定資料庫連線（編輯 .env）

# 7. 執行資料庫遷移
php artisan migrate

# 8. 執行資料庫種子
php artisan db:seed

# 9. 建構前端資源
npm run build

# 10. 啟動開發伺服器
php artisan serve
```

### 方法三：使用 Docker

```bash
# 使用 Laravel Sail
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail npm run build
```

---

## 環境設定

### 基本設定

```env
APP_NAME="My E-Commerce"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### 資料庫設定

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=itemwebsite
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 綠界金流設定

```env
ECPAY_MERCHANT_ID=your_merchant_id
ECPAY_HASH_KEY=your_hash_key
ECPAY_HASH_IV=your_hash_iv
ECPAY_ENV=stage  # stage 或 production
```

### 綠界物流設定

```env
ECPAY_LOGISTICS_MERCHANT_ID=your_logistics_merchant_id
ECPAY_LOGISTICS_HASH_KEY=your_logistics_hash_key
ECPAY_LOGISTICS_HASH_IV=your_logistics_hash_iv
```

---

## 資料庫架構

### ER Diagram

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   users     │     │  products   │     │ categories  │
├─────────────┤     ├─────────────┤     ├─────────────┤
│ id          │     │ id          │     │ id          │
│ name        │◄────│ user_id     │     │ name        │
│ email       │     │ category_id │────►│ parent_id   │
│ role        │     │ brand_id    │     └─────────────┘
└─────────────┘     │ name        │
       │            │ price       │     ┌─────────────┐
       │            │ stock       │     │   brands    │
       ▼            │ is_active   │     ├─────────────┤
┌─────────────┐     └─────────────┘     │ id          │
│   carts     │            │            │ name        │
├─────────────┤            │            │ logo        │
│ id          │            ▼            └─────────────┘
│ user_id     │     ┌─────────────┐
│ session_id  │     │ cart_items  │
└─────────────┘     ├─────────────┤
       │            │ cart_id     │
       ▼            │ product_id  │
┌─────────────┐     │ quantity    │
│   orders    │     │ price       │
├─────────────┤     └─────────────┘
│ id          │
│ user_id     │     ┌─────────────┐
│ order_number│     │ order_items │
│ status      │     ├─────────────┤
│ total       │◄────│ order_id    │
│ shipping_*  │     │ product_id  │
└─────────────┘     │ quantity    │
       │            │ price       │
       ▼            └─────────────┘
┌─────────────┐
│  payments   │
├─────────────┤
│ order_id    │
│ trade_no    │
│ amount      │
│ status      │
└─────────────┘
```

### 主要資料表

| 資料表 | 說明 |
|--------|------|
| `users` | 使用者（含角色：admin/customer/merchant）|
| `products` | 商品 |
| `categories` | 商品分類 |
| `brands` | 品牌 |
| `tags` | 標籤 |
| `product_tag` | 商品-標籤關聯 |
| `carts` | 購物車 |
| `cart_items` | 購物車項目 |
| `orders` | 訂單 |
| `order_items` | 訂單項目 |
| `payments` | 付款記錄 |
| `activity_log` | 活動日誌 |

### Stored Procedures

| 名稱 | 功能 |
|------|------|
| `sp_create_order()` | 建立訂單（含庫存扣減、購物車清空）|
| `sp_cancel_order()` | 取消訂單（含庫存回補）|

### Triggers

| 名稱 | 觸發時機 | 功能 |
|------|----------|------|
| `trg_deduct_stock_on_order_item` | INSERT on order_items | 自動扣減庫存 |
| `trg_restore_stock_on_order_item_delete` | DELETE on order_items | 自動回補庫存 |

---

## API 文件

### 認證

所有 API 使用 Laravel Sanctum Bearer Token 認證。

```
Authorization: Bearer {your-token}
```

### 商品 API

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/products` | 取得商品列表 |
| GET | `/api/products/{id}` | 取得單一商品 |
| POST | `/api/products` | 新增商品 |
| PUT | `/api/products/{id}` | 更新商品 |
| DELETE | `/api/products/{id}` | 刪除商品 |

### 購物車 API

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/cart` | 取得購物車內容 |
| POST | `/api/cart` | 新增商品至購物車 |
| PUT | `/api/cart/{id}` | 更新購物車項目數量 |
| DELETE | `/api/cart/{id}` | 移除購物車項目 |
| POST | `/api/cart/clear` | 清空購物車 |
| POST | `/api/cart/validate` | 驗證購物車庫存 |
| POST | `/api/cart/merge` | 合併訪客購物車 |

### 結帳 API

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/checkout/data` | 取得結帳資料 |
| POST | `/api/checkout/order` | 建立訂單 |

### 訂單 API

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/orders` | 取得使用者訂單 |
| GET | `/api/orders/{orderNumber}` | 取得訂單詳情 |

### 搜尋 API

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/search?q={keyword}` | 搜尋商品 |

---

## 專案結構

```
itemWebsite/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Cart/              # 購物車相關
│   │       ├── Order/             # 訂單相關
│   │       ├── Payment/           # 金流相關
│   │       ├── Product/           # 商品相關
│   │       └── Merchant/          # 商家相關
│   ├── Models/
│   │   ├── Cart/                  # Cart, CartItem
│   │   ├── Order/                 # Order, OrderItem
│   │   ├── Payment/               # Payment
│   │   ├── Product/               # Product, Category, Tag
│   │   └── Front/                 # Brand
│   ├── Services/                  # 商業邏輯層
│   └── Repositories/              # 資料存取層
├── database/
│   ├── migrations/                # 資料庫遷移
│   └── seeders/                   # 資料種子
├── resources/
│   ├── js/
│   │   ├── Components/            # Vue 元件
│   │   ├── Layouts/               # 版面配置
│   │   └── Pages/                 # 頁面元件
│   └── views/                     # Blade 模板
├── routes/
│   ├── web.php                    # Web 路由
│   └── api.php                    # API 路由
└── tests/                         # 測試檔案
```

---

## 開發指令

### 日常開發

```bash
# 啟動開發環境（並行執行 Laravel + Vite + Queue + Logs）
composer dev

# 或分別啟動
php artisan serve          # 啟動 Laravel 伺服器
npm run dev                # 啟動 Vite 開發伺服器
php artisan queue:work     # 啟動 Queue Worker
php artisan pail           # 即時 Log 檢視
```

### Artisan 指令

```bash
# 資料庫
php artisan migrate                  # 執行遷移
php artisan migrate:fresh --seed     # 重建資料庫並種子
php artisan db:seed                  # 執行種子

# 快取
php artisan cache:clear              # 清除快取
php artisan config:clear             # 清除設定快取
php artisan route:clear              # 清除路由快取
php artisan view:clear               # 清除視圖快取

# 優化
php artisan optimize                 # 快取設定與路由
php artisan optimize:clear           # 清除所有快取
```

### 程式碼品質

```bash
# 程式碼風格修正
./vendor/bin/pint

# 靜態分析（如有安裝 PHPStan）
./vendor/bin/phpstan analyse
```

---

## 測試

### 執行測試

```bash
# 執行所有測試
php artisan test

# 或使用 PHPUnit
./vendor/bin/phpunit

# 執行特定測試
php artisan test --filter=OrderTest

# 顯示測試覆蓋率
php artisan test --coverage
```

### 測試環境設定

測試使用 SQLite 記憶體資料庫，設定於 `phpunit.xml`：

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## 部署

### 正式環境設定

```env
APP_ENV=production
APP_DEBUG=false
ECPAY_ENV=production
```

### 部署步驟

```bash
# 1. 安裝依賴（不含開發套件）
composer install --no-dev --optimize-autoloader

# 2. 建構前端
npm ci
npm run build

# 3. 快取設定
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. 執行遷移
php artisan migrate --force

# 5. 重啟 Queue Worker
php artisan queue:restart
```

### Nginx 設定範例

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/itemWebsite/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 授權

本專案採用 MIT License 授權。
