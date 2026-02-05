# 購物車系統文件 (Shopping Cart System)

## 目錄
- [系統架構](#系統架構)
- [資料庫結構](#資料庫結構)
- [API 端點](#api-端點)
- [核心功能](#核心功能)
- [使用範例](#使用範例)
- [錯誤處理](#錯誤處理)
- [最佳實踐](#最佳實踐)

---

## 系統架構

購物車系統採用 **Repository Pattern** 架構，分為三層：

```
Controller (控制器層)
    ↓
Service (業務邏輯層)
    ↓
Repository (資料存取層)
    ↓
Model (資料模型層)
```

### 檔案結構

```
app/
├── Http/Controllers/
│   └── CartController.php          # API 控制器
├── Services/
│   └── CartService.php              # 業務邏輯處理
├── Repositories/
│   ├── CartRepository.php           # 資料庫操作
│   └── Contracts/
│       └── CartRepositoryInterface.php  # Repository 介面
└── Models/
    └── Cart/
        ├── Cart.php                 # 購物車模型
        └── cartItem.php             # 購物車項目模型
```

---

## 資料庫結構

### carts 表 (購物車主表)

| 欄位 | 類型 | 說明 |
|------|------|------|
| id | bigint | 主鍵 |
| user_id | bigint (nullable) | 會員 ID (登入使用者) |
| session_id | string (nullable) | Session ID (訪客使用者) |
| created_at | timestamp | 建立時間 |
| updated_at | timestamp | 更新時間 |

**特性**:
- 支援會員和訪客兩種模式
- 登入使用者使用 `user_id`
- 訪客使用者使用 `session_id`

### cart_items 表 (購物車項目表)

| 欄位 | 類型 | 說明 |
|------|------|------|
| id | bigint | 主鍵 |
| cart_id | bigint | 購物車 ID (外鍵) |
| product_id | bigint | 商品 ID (外鍵) |
| product_variant_id | bigint (nullable) | 商品規格 ID (外鍵) |
| quantity | integer | 數量 |
| price | decimal(10,2) | 購買時的單價 |
| created_at | timestamp | 建立時間 |
| updated_at | timestamp | 更新時間 |

**特性**:
- 支援有規格和無規格商品
- 記錄加入購物車時的價格（避免價格變動影響）

---

## API 端點

### 基礎路徑
```
/api/cart
```

### 端點列表

#### 1. 取得購物車內容
```http
GET /api/cart
```

**回應範例**:
```json
{
    "success": true,
    "data": {
        "cart_id": 1,
        "items": [
            {
                "id": 1,
                "product_id": 10,
                "product_variant_id": 5,
                "quantity": 2,
                "price": 299.00,
                "display_name": "iPhone 14 Pro (太空黑, 256GB)",
                "image_url": "https://example.com/image.jpg",
                "subtotal": 598.00
            }
        ],
        "total_quantity": 2,
        "total_price": 598.00,
        "item_count": 1
    }
}
```

#### 2. 加入商品到購物車
```http
POST /api/cart
```

**請求參數**:
```json
{
    "product_id": 10,              // 必填，商品 ID
    "product_variant_id": 5,       // 選填，商品規格 ID
    "quantity": 2                   // 必填，數量（最小為 1）
}
```

**回應範例**:
```json
{
    "success": true,
    "message": "已加入購物車",
    "data": {
        "cart_id": 1,
        "items": [...],
        "total_quantity": 2,
        "total_price": 598.00,
        "item_count": 1
    }
}
```

#### 3. 更新購物車項目數量
```http
PUT /api/cart/{cartItemId}
```

**請求參數**:
```json
{
    "quantity": 3    // 必填，新數量（0 表示刪除）
}
```

**回應範例**:
```json
{
    "success": true,
    "message": "已更新數量",
    "data": {
        "cart_id": 1,
        "items": [...],
        "total_quantity": 3,
        "total_price": 897.00,
        "item_count": 1
    }
}
```

#### 4. 移除購物車項目
```http
DELETE /api/cart/{cartItemId}
```

**回應範例**:
```json
{
    "success": true,
    "message": "已移除商品",
    "data": {
        "cart_id": 1,
        "items": [],
        "total_quantity": 0,
        "total_price": 0,
        "item_count": 0
    }
}
```

#### 5. 清空購物車
```http
DELETE /api/cart
```

**回應範例**:
```json
{
    "success": true,
    "message": "已清空購物車",
    "data": {
        "cart_id": 1,
        "items": [],
        "total_quantity": 0,
        "total_price": 0,
        "item_count": 0
    }
}
```

#### 6. 驗證購物車庫存
```http
POST /api/cart/validate
```

**用途**: 結帳前驗證所有商品庫存是否充足

**回應範例（成功）**:
```json
{
    "success": true,
    "message": "購物車驗證通過"
}
```

**回應範例（失敗）**:
```json
{
    "success": false,
    "message": "iPhone 14 Pro (太空黑, 256GB) 庫存不足（剩餘 1 件）、MacBook Pro 目前無法購買"
}
```

#### 7. 合併訪客購物車
```http
POST /api/cart/merge
```

**用途**: 使用者登入時，將訪客購物車合併到會員購物車

**請求參數**:
```json
{
    "guest_session_id": "abc123xyz"    // 必填，訪客 Session ID
}
```

**回應範例**:
```json
{
    "success": true,
    "message": "購物車已合併",
    "data": {
        "cart_id": 2,
        "items": [...],
        "total_quantity": 5,
        "total_price": 1500.00,
        "item_count": 3
    }
}
```

---

## 核心功能

### 1. 購物車識別機制

系統支援兩種購物車模式：

**訪客模式** (未登入):
- 使用 Laravel Session ID 識別購物車
- Session ID 儲存在 `carts.session_id`
- 關閉瀏覽器後 Session 可能失效

**會員模式** (已登入):
- 使用會員 ID 識別購物車
- User ID 儲存在 `carts.user_id`
- 購物車資料持久保存

### 2. 商品規格支援

支援兩種商品類型：

**無規格商品**:
```php
$cartService->addToCart(
    productId: 10,
    productVariantId: null,  // 無規格
    quantity: 1
);
```

**有規格商品**:
```php
$cartService->addToCart(
    productId: 10,
    productVariantId: 5,     // 指定規格 ID
    quantity: 1
);
```

### 3. 庫存檢查機制

加入購物車時會自動檢查：
- ✅ 商品是否存在
- ✅ 商品是否上架 (`is_active`)
- ✅ 規格是否啟用 (`is_active`)
- ✅ 庫存是否充足
- ✅ 規格是否屬於該商品

**庫存邏輯**:
```
有規格商品 → 檢查 product_variants.stock
無規格商品 → 檢查 products.stock
```

### 4. 價格快照機制

購物車項目會記錄加入時的價格：
- 儲存在 `cart_items.price`
- 避免商品調價影響已加入購物車的商品
- 結帳時使用快照價格計算

### 5. 自動合併機制

**同商品同規格自動合併**:
```
購物車現有: iPhone 14 Pro (黑色, 256GB) × 2
新加入:     iPhone 14 Pro (黑色, 256GB) × 1
結果:       iPhone 14 Pro (黑色, 256GB) × 3 (自動合併)
```

**不同規格分開顯示**:
```
購物車有: iPhone 14 Pro (黑色, 256GB) × 2
新加入:   iPhone 14 Pro (白色, 256GB) × 1
結果:     兩個獨立項目
```

### 6. 訪客購物車合併

使用者登入時自動觸發：

```php
// 前端在登入成功後呼叫
$guestSessionId = Session::getId();  // 取得登入前的 Session ID
$cartService->mergeGuestCart($guestSessionId);
```

**合併邏輯**:
1. 找到訪客購物車（根據 session_id）
2. 找到會員購物車（根據 user_id）
3. 逐一比對項目：
   - 相同商品+規格 → 數量相加
   - 不同商品/規格 → 直接加入
4. 刪除訪客購物車

### 7. 權限驗證

每次操作購物車項目時會驗證：
- 登入使用者：檢查 `cart.user_id` 是否匹配
- 訪客：檢查 `cart.session_id` 是否匹配
- 防止使用者操作他人購物車

---

## 使用範例

### 前端範例（Vue.js / Axios）

#### 1. 載入購物車
```javascript
async function loadCart() {
    try {
        const response = await axios.get('/api/cart');
        if (response.data.success) {
            this.cart = response.data.data;
            this.totalItems = response.data.data.item_count;
            this.totalPrice = response.data.data.total_price;
        }
    } catch (error) {
        console.error('載入購物車失敗', error);
    }
}
```

#### 2. 加入購物車
```javascript
async function addToCart(productId, variantId = null, quantity = 1) {
    try {
        const response = await axios.post('/api/cart', {
            product_id: productId,
            product_variant_id: variantId,
            quantity: quantity
        });

        if (response.data.success) {
            alert(response.data.message);  // "已加入購物車"
            this.loadCart();  // 重新載入購物車
        }
    } catch (error) {
        alert(error.response.data.message);  // 顯示錯誤訊息
    }
}
```

#### 3. 更新數量
```javascript
async function updateQuantity(cartItemId, newQuantity) {
    try {
        const response = await axios.put(`/api/cart/${cartItemId}`, {
            quantity: newQuantity
        });

        if (response.data.success) {
            this.cart = response.data.data;
        }
    } catch (error) {
        alert(error.response.data.message);
    }
}
```

#### 4. 移除項目
```javascript
async function removeItem(cartItemId) {
    if (!confirm('確定要移除此商品？')) return;

    try {
        const response = await axios.delete(`/api/cart/${cartItemId}`);
        if (response.data.success) {
            this.cart = response.data.data;
        }
    } catch (error) {
        alert('移除失敗');
    }
}
```

#### 5. 結帳前驗證
```javascript
async function proceedToCheckout() {
    try {
        // 先驗證庫存
        const validation = await axios.post('/api/cart/validate');

        if (validation.data.success) {
            // 驗證通過，前往結帳頁
            this.$router.push('/checkout');
        }
    } catch (error) {
        // 顯示庫存錯誤訊息
        alert(error.response.data.message);
    }
}
```

#### 6. 登入後合併購物車
```javascript
async function login(username, password) {
    // 登入前先取得 Session ID
    const guestSessionId = this.getSessionId();

    // 執行登入
    const loginResponse = await axios.post('/api/login', {
        username,
        password
    });

    if (loginResponse.data.success) {
        // 登入成功後合併購物車
        try {
            await axios.post('/api/cart/merge', {
                guest_session_id: guestSessionId
            });

            // 重新載入購物車
            this.loadCart();
        } catch (error) {
            console.error('購物車合併失敗', error);
        }
    }
}
```

---

## 錯誤處理

### 常見錯誤訊息

| HTTP 狀態碼 | 錯誤訊息 | 說明 |
|------------|---------|------|
| 400 | 商品規格不符 | 規格 ID 不屬於該商品 |
| 400 | 此規格目前無法購買 | 規格已停用 |
| 400 | 此商品目前無法購買 | 商品已下架 |
| 400 | 庫存不足 | 商品或規格庫存不足 |
| 400 | 數量不能為負數 | 輸入的數量 < 0 |
| 400 | 無權操作此購物車項目 | 嘗試操作他人購物車 |
| 404 | 商品不存在 | Product ID 不存在 |
| 404 | 商品規格不存在 | Product Variant ID 不存在 |
| 422 | 請選擇商品 | product_id 未提供 |
| 422 | 請輸入數量 | quantity 未提供 |
| 422 | 數量至少為 1 | quantity < 1 |

### 前端錯誤處理建議

```javascript
try {
    const response = await axios.post('/api/cart', data);
    // 處理成功
} catch (error) {
    if (error.response) {
        // 伺服器回應錯誤
        const status = error.response.status;
        const message = error.response.data.message;

        switch (status) {
            case 400:
                alert(`操作失敗：${message}`);
                break;
            case 404:
                alert('找不到商品');
                break;
            case 422:
                alert(`輸入錯誤：${message}`);
                break;
            default:
                alert('系統錯誤，請稍後再試');
        }
    } else {
        // 網路錯誤
        alert('網路連線失敗');
    }
}
```

---

## 最佳實踐

### 1. 前端顯示建議

**購物車圖示顯示項目總數**:
```javascript
// 顯示購物車內商品種類數量
<span class="cart-badge">{{ cart.item_count }}</span>

// 或顯示商品總數量
<span class="cart-badge">{{ cart.total_quantity }}</span>
```

**即時更新購物車**:
```javascript
// 每次操作後都重新載入購物車
async function addToCart() {
    await axios.post('/api/cart', data);
    await this.loadCart();  // 重新載入
}
```

### 2. 效能優化

**使用 Loading 狀態**:
```javascript
data() {
    return {
        isLoadingCart: false
    }
},
methods: {
    async loadCart() {
        this.isLoadingCart = true;
        try {
            const response = await axios.get('/api/cart');
            this.cart = response.data.data;
        } finally {
            this.isLoadingCart = false;
        }
    }
}
```

### 3. 使用者體驗

**加入購物車前確認規格**:
```javascript
function addToCart() {
    if (this.hasVariants && !this.selectedVariantId) {
        alert('請選擇商品規格');
        return;
    }

    // 執行加入購物車
}
```

**數量選擇器限制**:
```vue
<input
    type="number"
    :value="item.quantity"
    :max="item.max_stock"
    min="1"
    @change="updateQuantity(item.id, $event.target.value)"
>
```

### 4. 安全性考量

**不要信任前端價格**:
```javascript
// ❌ 錯誤：從前端傳送價格
await axios.post('/api/cart', {
    product_id: 10,
    quantity: 1,
    price: 299  // 危險！可能被竄改
});

// ✅ 正確：價格由後端決定
await axios.post('/api/cart', {
    product_id: 10,
    quantity: 1
    // 價格由後端自動取得
});
```

**驗證庫存**:
```javascript
// 結帳前一定要驗證庫存
async function checkout() {
    try {
        await axios.post('/api/cart/validate');
        // 驗證通過才能結帳
        window.location.href = '/checkout';
    } catch (error) {
        alert(error.response.data.message);
    }
}
```

### 5. 本地快取建議

```javascript
// 使用 Vuex 或 Pinia 儲存購物車狀態
const store = createStore({
    state: {
        cart: null,
        lastUpdated: null
    },
    actions: {
        async fetchCart({ commit }) {
            const response = await axios.get('/api/cart');
            commit('SET_CART', response.data.data);
            commit('SET_LAST_UPDATED', Date.now());
        }
    }
});
```

---

## 相依套件

- Laravel Framework 10.x
- PHP 8.1+
- MySQL 8.0+

## 相關文件

- [商品系統文件](PRODUCT_README.md)
- [訂單系統文件](ORDER_README.md)
- [API 完整文件](API_DOCUMENTATION.md)

---

## 授權

Copyright © 2026 Your Company. All rights reserved.
