<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\AboutUsController;
use App\Http\Controllers\Front\BrandStoryController;
use App\Http\Controllers\Product\CommodityController;
use App\Http\Controllers\Promotion\DiscountController;
use App\Http\Controllers\Cart\CheckoutController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Payment\EcpayController;
use App\Http\Controllers\Logistics\EcpayLogisticsController;
use App\Http\Controllers\Logistics\LogisticsManageController;
use App\Http\Controllers\Product\ProductManageController;
use App\Http\Controllers\Auth\MerchantAuthController;
use App\Http\Controllers\Merchant\MerchantDashboardController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Order\OrderManageController;
use App\Http\Controllers\Payment\PaymentManageController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/aboutus', [AboutUsController::class, 'index'])->name('aboutus.index');
Route::get('/brandstory', [BrandStoryController::class, 'index'])->name('brandstory.index');
Route::get('/commodity', [CommodityController::class, 'index'])->name('commodity.index');
Route::get('/discount', [DiscountController::class, 'index'])->name('discount.index');

// 結帳頁面路由
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

// 訂單頁面路由（需登入）
Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
});

// 商家認證路由
Route::prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/login', [MerchantAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [MerchantAuthController::class, 'login']);
    Route::get('/register', [MerchantAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [MerchantAuthController::class, 'register']);
    Route::post('/logout', [MerchantAuthController::class, 'logout'])->name('logout');
});

// 商家後台路由（需登入）
Route::middleware(['auth'])->prefix('merchant')->name('merchant.')->group(function () {
    Route::get('/dashboard', [MerchantDashboardController::class, 'index'])->name('dashboard');
});

// 商品管理路由（需登入）
Route::middleware(['auth'])->prefix('my-products')->name('my-products.')->group(function () {
    Route::get('/', [ProductManageController::class, 'index'])->name('index');
    Route::get('/create', [ProductManageController::class, 'create'])->name('create');
    Route::post('/', [ProductManageController::class, 'store'])->name('store');
    Route::get('/{product}/edit', [ProductManageController::class, 'edit'])->name('edit');
    Route::put('/{product}', [ProductManageController::class, 'update'])->name('update');
    Route::get('/{product}/delete', [ProductManageController::class, 'delete'])->name('delete');
    Route::delete('/{product}', [ProductManageController::class, 'destroy'])->name('destroy');
    Route::post('/{product}/toggle-status', [ProductManageController::class, 'toggleStatus'])->name('toggle-status');
});

// 庫存管理路由（需登入）
Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::post('/{product}/adjust', [InventoryController::class, 'adjust'])->name('adjust');
});

// 訂單管理路由（需登入）
Route::middleware(['auth'])->prefix('manage-orders')->name('manage-orders.')->group(function () {
    Route::get('/', [OrderManageController::class, 'index'])->name('index');
    Route::get('/{order}', [OrderManageController::class, 'show'])->name('show');
    Route::post('/{order}/status', [OrderManageController::class, 'updateStatus'])->name('update-status');
    Route::post('/{order}/note', [OrderManageController::class, 'addNote'])->name('add-note');
});

// 金流管理路由（需登入）
Route::middleware(['auth'])->prefix('manage-payments')->name('manage-payments.')->group(function () {
    Route::get('/', [PaymentManageController::class, 'index'])->name('index');
    Route::get('/{payment}', [PaymentManageController::class, 'show'])->name('show');
    Route::post('/{payment}/status', [PaymentManageController::class, 'updateStatus'])->name('update-status');
    Route::post('/{payment}/refund', [PaymentManageController::class, 'refund'])->name('refund');
});

// 物流管理路由（需登入）
Route::middleware(['auth'])->prefix('manage-logistics')->name('manage-logistics.')->group(function () {
    Route::get('/', [LogisticsManageController::class, 'index'])->name('index');
    Route::post('/batch-create-shipment', [LogisticsManageController::class, 'batchCreateShipment'])->name('batch-create-shipment');
    Route::post('/{order}/create-shipment', [LogisticsManageController::class, 'createShipment'])->name('create-shipment');
    Route::get('/{order}/status', [LogisticsManageController::class, 'queryStatus'])->name('status');
    Route::post('/{order}/update-status', [LogisticsManageController::class, 'updateStatus'])->name('update-status');
});

// 臨時調試路由
Route::get('/debug-products', function () {
    $products = DB::table('products')->where('id', 53)->first();
    return response()->json([
        'raw_data' => $products,
        'price_type' => gettype($products->price),
        'price_value' => $products->price,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// 綠界金流路由（需要 session 的路由保留在 web.php）
// notify 和 callback 已移至 api.php（不需要 session）
Route::prefix('ecpay')->name('ecpay.')->group(function () {
    // 發起付款（需登入）
    Route::match(['get', 'post'], '/checkout', [EcpayController::class, 'checkout'])
        ->middleware('auth')
        ->name('checkout');

    // 付款結果頁面（GET 重定向，保留 session）
    Route::get('/result', [EcpayController::class, 'result'])->name('result');

    // 返回商店
    Route::get('/return', [EcpayController::class, 'return'])->name('return');

    // 查詢付款狀態
    Route::get('/status', [EcpayController::class, 'queryStatus'])
        ->middleware('auth')
        ->name('status');
});

// 綠界物流路由
Route::prefix('ecpay-logistics')->name('ecpay-logistics.')->group(function () {
    // 建立物流單（需登入）
    Route::post('/create/{order}', [EcpayLogisticsController::class, 'create'])
        ->middleware('auth')
        ->name('create');

    // 物流狀態通知（綠界回調）
    // CSRF 排除已在 bootstrap/app.php 中設定
    Route::post('/status-notify', [EcpayLogisticsController::class, 'statusNotify'])
        ->name('status-notify');

    // 查詢物流狀態（需登入）
    Route::get('/query/{order}', [EcpayLogisticsController::class, 'query'])
        ->middleware('auth')
        ->name('query');
});
