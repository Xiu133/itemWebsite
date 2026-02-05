<?php

namespace App\Repositories\Contracts\Cart;

interface CartRepositoryInterface
{
    // 根據使用者或 session 取得購物車
    public function getCartByUser($userId);

    public function getCartBySession($sessionId);

    public function getOrCreateCart($userId = null, $sessionId = null);

    // 購物車項目操作
    public function addItem($cartId, $productId, $quantity, $price);

    public function updateItemQuantity($cartItemId, $quantity);

    public function removeItem($cartItemId);

    public function clearCart($cartId);

    // 查詢
    public function getCartItems($cartId);

    public function findCartItem($cartId, $productId);

    public function getCartSummary($cartId);

    // 合併購物車（訪客登入後）
    public function mergeCarts($guestCartId, $userCartId);
}
