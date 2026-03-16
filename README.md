# MONO E-Commerce Platform

面向台灣市場的全功能電商平台，基於 Laravel 12 + Vue 3 + Inertia.js 構建，整合綠界金流與物流系統。

---

## 目錄

- [系統架構總覽](#系統架構總覽)
- [技術棧](#技術棧)
- [架構設計思路](#架構設計思路)
- [分層架構](#分層架構)
- [資料庫設計](#資料庫設計)
- [認證體系](#認證體系)
- [金流架構](#金流架構)
- [物流架構](#物流架構)
- [前端架構](#前端架構)
- [API 設計](#api-設計)
- [部署架構](#部署架構)
- [專案結構](#專案結構)
- [開發與部署指南](#開發與部署指南)

---

## 系統架構總覽

```
                    ┌──────────────────────────────────────────────────┐
                    │                   Client Layer                    │
                    │  ┌──────────────┐  ┌──────────────────────────┐  │
                    │  │  Inertia SPA │  │   Blade + Vanilla JS     │  │
                    │  │  (Vue 3)     │  │   (多入口 Vite 打包)      │  │
                    │  └──────┬───────┘  └────────────┬─────────────┘  │
                    └─────────┼───────────────────────┼────────────────┘
                              │                       │
                    ┌─────────▼───────────────────────▼────────────────┐
                    │              Laravel 12 Application               │
                    │  ┌────────────────────────────────────────────┐  │
                    │  │          Routes (web.php / api.php)         │  │
                    │  └────────────────────┬───────────────────────┘  │
                    │  ┌────────────────────▼───────────────────────┐  │
                    │  │              Controllers                    │  │
                    │  │  (HTTP 處理、請求驗證、回應格式化)            │  │
                    │  └────────────────────┬───────────────────────┘  │
                    │  ┌────────────────────▼───────────────────────┐  │
                    │  │               Services                     │  │
                    │  │  (商業邏輯、流程編排、跨 Repository 協作)     │  │
                    │  └────────────────────┬───────────────────────┘  │
                    │  ┌────────────────────▼───────────────────────┐  │
                    │  │         Repositories (Interface)            │  │
                    │  │  (資料存取抽象、查詢封裝)                     │  │
                    │  └────────────────────┬───────────────────────┘  │
                    │  ┌────────────────────▼───────────────────────┐  │
                    │  │           Eloquent Models                   │  │
                    │  │  (ORM 映射、關聯定義、Scope)                  │  │
                    │  └────────────────────┬───────────────────────┘  │
                    └───────────────────────┼──────────────────────────┘
                    ┌───────────────────────▼──────────────────────────┐
                    │            PostgreSQL 16                          │
                    │  Stored Procedures │ Triggers │ Row-Level Lock    │
                    └──────────────────────────────────────────────────┘
```

---

## 技術棧

### 後端

| 技術 | 版本 | 選型理由 |
|------|------|----------|
| **PHP** | 8.2+ | 原生 Fiber 支援、Enum、唯讀屬性，生態成熟 |
| **Laravel** | 12.0 | 完整的生態系統、Eloquent ORM、內建認證與佇列 |
| **PostgreSQL** | 16 | 支援 Stored Procedure / Trigger / Row-Level Locking，確保資料一致性 |
| **Laravel Jetstream** | 5.4 | 提供 2FA、Session 管理、Profile 管理等開箱即用功能 |
| **Laravel Sanctum** | 4.0 | 輕量 SPA / API Token 認證方案 |
| **Inertia.js (Server)** | 2.0 | 消除傳統 SPA 需要獨立 API 的開銷，Server-driven SPA |
| **Spatie Activity Log** | 4.11 | 結構化的操作審計日誌 |
| **ECPay SDK** | 1.3 | 台灣主流金流/物流整合 |
| **Ziggy** | 2.0 | 前端直接使用 Laravel 具名路由，路由維護單一來源 |

### 前端

| 技術 | 版本 | 選型理由 |
|------|------|----------|
| **Vue.js** | 3.3 | Composition API、響應式系統、與 Inertia.js 深度整合 |
| **Tailwind CSS** | 3.4 | Utility-first 設計，快速原型開發，與元件化開發互補 |
| **Vite** | 7.0 | 原生 ESM 開發伺服器、極速 HMR、多入口打包 |
| **Axios** | 1.11 | 攔截器機制方便統一處理認證 Token 與錯誤 |

### 基礎設施

| 技術 | 用途 |
|------|------|
| **Docker** | 容器化部署（PHP-FPM + Nginx + Supervisor） |
| **Google Cloud Run** | 生產環境，Serverless 容器託管 |
| **Database Queue** | 佇列驅動（基於 PostgreSQL，無需額外佇列服務） |
| **Database Cache** | 快取驅動（Redis 可選） |
| **OPcache** | PHP 字節碼快取，生產環境預熱 |

---

## 架構設計思路

### 1. 為什麼選擇 Inertia.js 而非傳統 SPA + API？

```
傳統 SPA 方案：
  Vue App ──HTTP──► Laravel API ──► DB
  ❌ 需要維護獨立的 API 層（序列化、版本控制、CORS）
  ❌ 認證複雜度高（Token 生命週期管理）
  ❌ SEO 需要額外的 SSR 方案

Inertia.js 方案（本專案）：
  Vue App ◄──Inertia──► Laravel Controller ──► DB
  ✅ Controller 直接回傳 Vue Props，無需序列化層
  ✅ 共用 Session 認證，無需管理 Token
  ✅ 全頁面路由由 Laravel 控制，路由即權限
  ✅ 保有 SPA 的無刷新體驗
```

**取捨**：部分頁面（商品列表、首頁）使用傳統 Blade 模板渲染，以獲得更好的首次載入效能和 SEO。這形成了 **Inertia SPA + Blade 混合渲染** 的雙軌模式。

### 2. 為什麼用 Repository Pattern？

```
直接使用 Eloquent 的問題：
  Controller → Eloquent Model（業務邏輯散落在 Controller 中）

本專案的分層：
  Controller → Service → Repository(Interface) → Model

好處：
  ✅ Service 層封裝業務邏輯，Controller 只做 HTTP 處理
  ✅ Repository 介面化，方便單元測試 Mock
  ✅ 資料存取邏輯集中管理，避免重複查詢散落各處
```

### 3. 為什麼把關鍵邏輯下沉到資料庫層？

電商的核心挑戰是 **併發下的資料一致性**（超賣問題）。本專案將庫存扣減/回補邏輯下沉到 PostgreSQL：

```
應用層（Laravel）                    資料庫層（PostgreSQL）
┌────────────────┐               ┌────────────────────────────┐
│ CheckoutController │──呼叫──►│ sp_create_order()            │
│                │               │  ├─ 驗證購物車               │
│                │               │  ├─ 計算總金額               │
│                │               │  ├─ INSERT orders            │
│                │               │  ├─ INSERT order_items       │
│                │               │  │    └─► trg_deduct_stock   │
│                │               │  │        (FOR UPDATE 行鎖)   │
│                │               │  ├─ 清空購物車               │
│                │               │  └─ COMMIT / ROLLBACK        │
└────────────────┘               └────────────────────────────┘

優勢：
  ✅ 整個訂單建立在單一 DB Transaction 中完成，原子性保證
  ✅ Trigger + FOR UPDATE 行級鎖防止併發超賣
  ✅ 即使應用層崩潰，資料庫層保證資料一致性
  ✅ 比應用層樂觀鎖/悲觀鎖實作更可靠
```

### 4. 購物車的雙模式設計

```
訪客模式：                          會員模式：
┌──────────┐                      ┌──────────┐
│ Session ID│──────┐              │ User ID  │──────┐
└──────────┘      ▼              └──────────┘      ▼
              ┌────────┐                        ┌────────┐
              │ carts  │                        │ carts  │
              │ table  │                        │ table  │
              └────────┘                        └────────┘

登入時自動合併：
  Session Cart ──merge──► User Cart
  ✅ 訪客加入購物車的商品不會遺失
  ✅ 價格快照機制確保加入時的價格被記錄
```

---

## 分層架構

每個業務領域按 **Controller → Service → Repository → Model** 四層組織：

```
app/
├── Http/Controllers/
│   ├── Cart/           # CartController, CheckoutController
│   ├── Order/          # OrderController, OrderManageController
│   ├── Payment/        # EcpayController, PaymentManageController
│   ├── Product/        # ProductController, ProductManageController
│   ├── Logistics/      # EcpayLogisticsController, LogisticsManageController
│   ├── Merchant/       # MerchantDashboardController
│   ├── Promotion/      # DiscountController
│   ├── Inventory/      # InventoryController
│   └── Front/          # HomeController, AboutUsController, SearchController
│
├── Services/           # 業務邏輯（不直接操作 HTTP Request/Response）
│   ├── Cart/           # CartService
│   ├── Payment/        # EcpayService, PaymentManageService
│   ├── Logistics/      # EcpayLogisticsService
│   ├── Order/          # OrderService
│   ├── Product/        # ProductService
│   ├── Promotion/      # PromotionService
│   ├── Inventory/      # InventoryService
│   └── Auth/           # AuthService
│
├── Repositories/       # 資料存取層
│   ├── Contracts/      # Repository 介面定義
│   ├── Cart/           # CartRepository
│   ├── Order/          # OrderRepository
│   ├── Payment/        # PaymentRepository
│   ├── Product/        # ProductRepository
│   └── ...
│
└── Models/             # Eloquent ORM 模型
    ├── Cart/           # Cart, CartItem
    ├── Order/          # Order, OrderItem
    ├── Payment/        # Payment
    ├── Product/        # Product, Category, Tag
    └── Front/          # Brand
```

**呼叫規則**：
- Controller 只呼叫 Service，不直接存取 Repository 或 Model
- Service 透過 Repository Interface 操作資料，可協調多個 Repository
- Repository 封裝所有 Eloquent 查詢邏輯
- Model 只定義關聯、Scope、Accessor/Mutator

---

## 資料庫設計

### ER 關聯圖

```
┌──────────────┐      ┌───────────────┐      ┌──────────────┐
│    users     │      │   products    │      │  categories  │
├──────────────┤      ├───────────────┤      ├──────────────┤
│ id           │◄──┐  │ id            │  ┌──►│ id           │
│ name         │   │  │ user_id ──────┘  │   │ name         │
│ email        │   │  │ category_id ─────┘   │ slug         │
│ role (enum)  │   │  │ brand_id ────────┐   │ parent_id ───┤ (自關聯)
│ password     │   │  │ name            │   └──────────────┘
│ 2FA fields   │   │  │ price           │
└──────┬───────┘   │  │ stock           │   ┌──────────────┐
       │           │  │ is_active       │   │    brands    │
       │           │  │ (soft deletes)  │◄──┤ id           │
       │           │  └───────┬─────────┘   │ name         │
       │           │          │             │ logo         │
       ▼           │          │             └──────────────┘
┌──────────────┐   │    ┌─────▼────────┐
│    carts     │   │    │  product_tag │    ┌──────────────┐
├──────────────┤   │    ├──────────────┤    │     tags     │
│ id           │   │    │ product_id   │───►├──────────────┤
│ user_id ─────┘   │    │ tag_id       │    │ id           │
│ session_id   │   │    └──────────────┘    │ name         │
└──────┬───────┘   │                        └──────────────┘
       │           │
       ▼           │
┌──────────────┐   │
│  cart_items  │   │    ┌──────────────┐
├──────────────┤   │    │   orders     │
│ cart_id      │   │    ├──────────────┤
│ product_id   │   └────│ user_id      │
│ quantity     │        │ order_number │
│ price (快照) │        │ status       │
└──────────────┘        │ total        │
                        │ shipping_*   │
                        │ logistics_*  │
                        └──────┬───────┘
                               │
                ┌──────────────┼──────────────┐
                ▼                             ▼
        ┌──────────────┐              ┌──────────────┐
        │ order_items  │              │   payments   │
        ├──────────────┤              ├──────────────┤
        │ order_id     │              │ order_id     │
        │ product_id   │              │ trade_no     │
        │ quantity     │              │ amount       │
        │ price (快照) │              │ method       │
        │ name (快照)  │              │ status       │
        └──────────────┘              └──────────────┘
```

### 資料庫層邏輯

#### Stored Procedures

| 名稱 | 功能 | 設計考量 |
|------|------|----------|
| `sp_create_order()` | 原子性建立訂單 | 單一 Transaction 內完成：驗證購物車 → 計算金額 → 建立訂單 → 插入訂單項目 → 清空購物車 |
| `sp_cancel_order()` | 原子性取消訂單 | 單一 Transaction 內完成：更新訂單狀態 → 刪除訂單項目（觸發庫存回補 Trigger） |

#### Triggers

| 名稱 | 觸發時機 | 機制 |
|------|----------|------|
| `trg_deduct_stock_on_order_item` | `AFTER INSERT ON order_items` | `SELECT ... FOR UPDATE` 行級鎖 → 扣減庫存，併發安全 |
| `trg_restore_stock_on_order_item_delete` | `BEFORE DELETE ON order_items` | 回補對應商品庫存 |

#### 效能索引

針對高頻查詢路徑建立複合索引，定義於 `migrations/2026_02_14_000001_add_performance_indexes.php`。

---

## 認證體系

系統支援三種角色，採用不同認證策略：

```
┌─────────────────────────────────────────────────────┐
│                    users 資料表                       │
│                  role: enum                           │
│         ┌──────────┼──────────┐                      │
│         ▼          ▼          ▼                      │
│     customer    merchant     admin                   │
│                                                      │
│  ┌──────────┐ ┌───────────┐ ┌──────────┐            │
│  │ Jetstream│ │  Custom   │ │ Jetstream│            │
│  │ +Fortify │ │ Merchant  │ │ +Fortify │            │
│  │  認證     │ │  Auth     │ │  認證     │            │
│  └──────────┘ └───────────┘ └──────────┘            │
└─────────────────────────────────────────────────────┘

Web 認證：Session-based（Jetstream + Fortify）
API 認證：Sanctum Bearer Token
安全特性：2FA（Google Authenticator）、Email 驗證
```

**為什麼 Merchant 要獨立認證流程？**
- 商家註冊需要額外驗證欄位
- 商家登入需檢查角色，避免一般用戶誤入商家後台
- 獨立的登入/註冊頁面提供不同的 UX 流程

---

## 金流架構

### ECPay 支付流程

```
用戶瀏覽器                 Laravel                      ECPay 伺服器
    │                        │                              │
    │  1. POST /api/checkout/order                          │
    │───────────────────────►│ (建立訂單，呼叫 sp_create_order)
    │                        │                              │
    │  2. GET /ecpay/checkout                               │
    │───────────────────────►│                              │
    │  ◄─ 回傳 HTML 表單 ────│                              │
    │    (自動 POST 到 ECPay)                               │
    │                        │                              │
    │  3. 跳轉至 ECPay 付款頁面 ──────────────────────────►│
    │                        │                              │
    │                        │  4. POST /api/ecpay/notify   │
    │                        │◄─── Server-to-Server Webhook │
    │                        │  (更新 Payment 狀態)          │
    │                        │                              │
    │  5. ECPay POST 導回    │                              │
    │◄──────────────────────────────────────────────────────│
    │  → /api/ecpay/callback │                              │
    │  (JS 中繼頁面，GET 重導向)                             │
    │                        │                              │
    │  6. GET /ecpay/result  │                              │
    │───────────────────────►│                              │
    │  ◄─ 渲染結果頁面 ──────│                              │
```

**關鍵設計：Callback 中繼頁面**

ECPay 使用 POST 方式將用戶導回。由於瀏覽器 `SameSite=Lax` Cookie 政策，跨站 POST 不會攜帶 Session Cookie，導致用戶登入狀態遺失。解決方案：

```
ECPay POST → /api/ecpay/callback → 回傳 JS 頁面 → JS 執行 GET 重導向到 /ecpay/result
                                                    ↑
                                            GET 請求會攜帶 Cookie
                                            用戶登入狀態得以保留
```

### 支援的付款方式

| 方式 | 說明 |
|------|------|
| Credit | 信用卡（支援 3/6/12/18/24 期分期） |
| WebATM | 網路 ATM |
| ATM | 實體 ATM 轉帳 |
| CVS | 超商代碼繳費 |
| Barcode | 條碼繳費 |
| COD | 貨到付款 |

---

## 物流架構

### ECPay 物流整合

```
商家後台                    Laravel                      ECPay 物流
    │                        │                              │
    │  1. POST /ecpay-logistics/create/{order}              │
    │───────────────────────►│                              │
    │                        │─── 建立物流訂單 ────────────►│
    │                        │◄── 回傳物流單號 ─────────────│
    │                        │  (更新 Order logistics_*)    │
    │                        │                              │
    │                        │  2. POST /ecpay-logistics/status-notify
    │                        │◄─── 物流狀態更新 Webhook ────│
    │                        │  (更新訂單物流狀態)           │
    │                        │                              │
    │  3. GET /ecpay-logistics/query/{order}                │
    │───────────────────────►│─── 主動查詢狀態 ────────────►│
```

### 支援的物流商

| 類型 | 物流商 | 代碼 |
|------|--------|------|
| 宅配 | 黑貓宅急便 | TCAT |
| 宅配 | 宅配通 | ECAN |
| 超商取貨 | 全家 | FAMI |
| 超商取貨 | 7-ELEVEN | UNIMART |
| 超商取貨 | 萊爾富 | HILIFE |

### 物流狀態流轉

```
pending → created → picked_up → in_transit → delivered
                                           → failed
```

---

## 前端架構

### 雙軌渲染策略

本專案同時使用 **Inertia SPA** 和 **Blade 模板** 兩種渲染方式：

```
Inertia SPA 渲染（Vue 3）              Blade 模板渲染
─────────────────────                  ────────────────
適用場景：                              適用場景：
✅ 複雜互動頁面（認證、Profile）         ✅ 內容導向頁面（首頁、商品列表）
✅ 表單密集頁面（結帳、後台管理）         ✅ SEO 敏感頁面
✅ 需要 SPA 導航體驗的流程              ✅ 較簡單的展示型頁面

入口：resources/js/app.js              入口：多個獨立 JS 檔案
頁面：resources/js/Pages/              頁面：resources/views/
```

### Vite 多入口打包

```javascript
// vite.config.js — 20+ 入口點
input: [
    'resources/js/app.js',              // Inertia/Vue 主入口
    'resources/js/ecommerce/app.js',    // 首頁
    'resources/js/checkout/app.js',     // 結帳頁
    'resources/js/product/app.js',      // 商品頁
    'resources/js/merchant/dashboard.js', // 商家後台
    'resources/js/inventory/app.js',    // 庫存管理
    'resources/js/orders/manage.js',    // 訂單管理
    'resources/js/payments/manage.js',  // 付款管理
    'resources/js/logistics/manage.js', // 物流管理
    // ... 更多入口
]
```

**為什麼不統一用 Inertia SPA？**
- Blade 頁面可獨立載入，首次渲染更快
- SEO 友善（完整的伺服器端 HTML）
- 對於簡單的展示頁面，Blade 比 Vue 元件開發成本更低
- 各頁面的 JS/CSS 獨立打包，按需載入，減少不必要的 Bundle

---

## API 設計

### 路由分類

| 路由檔案 | 中介層 | 用途 |
|----------|--------|------|
| `routes/web.php` | `web`, `auth`, `verified` | Inertia 頁面、Blade 頁面、ECPay 表單 |
| `routes/api.php` | `web` / `auth:sanctum` | RESTful API（購物車、結帳、訂單、搜尋） |

### 主要 API 端點

**購物車（Session 認證）**

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/cart` | 取得購物車 |
| POST | `/api/cart` | 加入商品 |
| PUT | `/api/cart/{id}` | 更新數量 |
| DELETE | `/api/cart/{id}` | 移除項目 |
| POST | `/api/cart/clear` | 清空購物車 |
| POST | `/api/cart/validate` | 庫存驗證 |
| POST | `/api/cart/merge` | 合併訪客購物車 |

**結帳（需登入）**

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/checkout/data` | 取得結帳資料 |
| POST | `/api/checkout/order` | 建立訂單（呼叫 Stored Procedure） |

**訂單（Sanctum 認證）**

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/orders` | 取得用戶訂單列表 |
| GET | `/api/orders/{orderNumber}` | 取得訂單詳情 |

**搜尋**

| 方法 | 端點 | 說明 |
|------|------|------|
| GET | `/api/search?q={keyword}` | 全文搜尋商品 |

**ECPay Webhook（CSRF 排除）**

| 方法 | 端點 | 說明 |
|------|------|------|
| POST | `/api/ecpay/notify` | 金流付款結果通知 |
| POST | `/ecpay-logistics/status-notify` | 物流狀態更新通知 |

---

## 部署架構

### Docker 多階段建構

```dockerfile
# Stage 1: Composer 依賴安裝
FROM composer:latest AS composer
COPY composer.* ./
RUN composer install --no-dev --no-scripts

# Stage 2: 前端建構
FROM node:20-alpine AS frontend
COPY package*.json ./
RUN npm ci && npm run build

# Stage 3: 生產映像
FROM php:8.3-fpm
# 安裝 pdo_pgsql, pgsql, OPcache
# 複製 Composer 依賴 + 前端建構產物
# 設定 Nginx + Supervisor
```

### 容器內部架構

```
┌────────────────────────────────────────┐
│           Docker Container              │
│                                        │
│  ┌──────────┐      ┌──────────────┐   │
│  │  Nginx   │──────│   PHP-FPM    │   │
│  │ :8080    │ fcgi │              │   │
│  └──────────┘      └──────────────┘   │
│         ▲                              │
│         │          ┌──────────────┐   │
│     Supervisor ────│ Process Mgmt │   │
│                    └──────────────┘   │
└────────────────────────────────────────┘
```

### Google Cloud Run 部署

- 單一容器設計：Nginx + PHP-FPM 共存，由 Supervisor 管理
- 監聽 `$PORT`（Cloud Run 指定，預設 8080）
- 啟動時自動執行：`config:cache` → `route:cache` → `view:cache` → `migrate --force`

### Docker Compose（本地開發）

```yaml
services:
  app:      # PHP-FPM + Nginx + Supervisor
  nginx:    # Nginx Reverse Proxy (:8000)
  pgsql:    # PostgreSQL 16 (:5432)
```

---

## 專案結構

```
itemWebsite/
├── app/
│   ├── Http/Controllers/           # 依業務領域分類的控制器
│   │   ├── Auth/                   # 商家認證（MerchantAuthController）
│   │   ├── Cart/                   # 購物車、結帳
│   │   ├── Front/                  # 前台頁面（首頁、品牌故事、搜尋）
│   │   ├── Inventory/              # 庫存調整
│   │   ├── Logistics/              # 綠界物流
│   │   ├── Merchant/               # 商家後台
│   │   ├── Order/                  # 訂單（前台 + 管理）
│   │   ├── Payment/                # 綠界金流
│   │   ├── Product/                # 商品（CRUD + 分類 + 管理）
│   │   └── Promotion/              # 折扣促銷
│   ├── Models/                     # Eloquent 模型（依領域分資料夾）
│   ├── Services/                   # 業務邏輯層
│   ├── Repositories/               # 資料存取層
│   │   └── Contracts/              # Repository 介面
│   └── Providers/                  # 服務提供者（依賴注入綁定）
├── config/
│   └── ecpay.php                   # 綠界金流/物流設定
├── database/
│   ├── migrations/                 # 35+ 遷移檔（含 SP、Trigger、Index）
│   └── seeders/                    # 資料種子
├── docker/
│   ├── Dockerfile                  # 多階段建構
│   ├── docker-compose.yml          # 本地開發容器編排
│   ├── nginx.conf                  # 本地 Nginx 設定
│   ├── nginx-cloudrun.conf         # Cloud Run Nginx 設定
│   ├── supervisord.conf            # 進程管理設定
│   └── start.sh                    # 容器啟動腳本
├── resources/
│   ├── js/
│   │   ├── app.js                  # Inertia + Vue 主入口
│   │   ├── Pages/                  # Vue Inertia 頁面元件
│   │   ├── Components/             # 共用 Vue 元件（Jetstream UI）
│   │   ├── Layouts/                # 版面配置元件
│   │   ├── ecommerce/              # 首頁 JS
│   │   ├── checkout/               # 結帳頁 JS
│   │   ├── product/                # 商品頁 JS
│   │   ├── merchant/               # 商家後台 JS
│   │   └── ...                     # 其他頁面入口
│   └── views/                      # Blade 模板
├── routes/
│   ├── web.php                     # Web + Inertia 路由
│   └── api.php                     # REST API 路由
├── tests/                          # PHPUnit 測試（SQLite in-memory）
└── vite.config.js                  # Vite 多入口設定
```

---

## 開發與部署指南

### 系統需求

- PHP >= 8.2 + 擴展：pdo_pgsql, pgsql, bcmath, gd
- Composer >= 2.0
- Node.js >= 18
- PostgreSQL >= 14

### 本地開發

```bash
# 安裝依賴
composer install && npm install

# 環境設定
cp .env.example .env
php artisan key:generate
# 編輯 .env 設定資料庫與綠界金鑰

# 資料庫初始化
php artisan migrate --seed

# 啟動開發環境（並行：Laravel + Vite + Queue + Log）
composer dev
```

### Docker 開發

```bash
docker-compose -f docker/docker-compose.yml up -d
```

### 生產部署

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan migrate --force
php artisan queue:restart
```

### 測試

```bash
php artisan test                     # 執行所有測試
php artisan test --filter=OrderTest  # 指定測試
php artisan test --coverage          # 覆蓋率報告
```

測試使用 SQLite 記憶體資料庫，獨立於開發環境。

---

## 授權

MIT License
