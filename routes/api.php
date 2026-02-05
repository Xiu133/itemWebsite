<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Front\SearchController;
use App\Http\Controllers\Cart\CheckoutController;

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/form-data', [ProductController::class, 'formData']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/trashed/all', [ProductController::class, 'trashed']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::post('/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('/{id}/force', [ProductController::class, 'forceDestroy']);
});

// 購物車 API 路由（需要 session 支援）
Route::prefix('cart')->middleware(['web'])->group(function () {
    // 取得購物車內容
    Route::get('/', [CartController::class, 'index']);

    // 加入商品到購物車
    Route::post('/', [CartController::class, 'store']);

    // 更新購物車項目數量
    Route::put('/{cartItemId}', [CartController::class, 'update']);

    // 移除購物車項目
    Route::delete('/{cartItemId}', [CartController::class, 'destroy']);

    // 清空購物車
    Route::post('/clear', [CartController::class, 'clear']);

    // 驗證購物車庫存
    Route::post('/validate', [CartController::class, 'validate']);

    // 合併訪客購物車（登入時）
    Route::post('/merge', [CartController::class, 'merge']);

    // 同步前端購物車（結帳前）
    Route::post('/sync', [CartController::class, 'sync']);
});

// 搜尋 API 路由
Route::get('/search', [SearchController::class, 'search']);

// 結帳 API 路由（需要 session 支援）
Route::prefix('checkout')->middleware(['web'])->group(function () {
    Route::get('/data', [CheckoutController::class, 'getCheckoutData']);
    Route::post('/order', [CheckoutController::class, 'store']);
});

// 訂單 API 路由
Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CheckoutController::class, 'orders']);
    Route::get('/{orderNumber}', [CheckoutController::class, 'orderDetail']);
});
