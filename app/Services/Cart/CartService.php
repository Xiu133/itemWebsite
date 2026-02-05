<?php

namespace App\Services\Cart;

use App\Repositories\Contracts\Cart\CartRepositoryInterface;
use App\Models\Product\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * 取得當前購物車
     */
    public function getCurrentCart()
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        $cart = $this->cartRepository->getOrCreateCart($userId, $sessionId);

        return $this->cartRepository->getCartSummary($cart->id);
    }

    /**
     * 加入商品到購物車
     */
    public function addToCart($productId, $quantity = 1)
    {
        // 驗證商品存在
        $product = Product::findOrFail($productId);

        // 檢查商品是否上架
        if (!$product->is_active) {
            throw new \Exception('此商品目前無法購買');
        }

        // 檢查庫存
        if ($product->stock < $quantity) {
            throw new \Exception('庫存不足');
        }

        $price = $product->price;

        // 取得或建立購物車
        $userId = Auth::id();
        $sessionId = Session::getId();
        $cart = $this->cartRepository->getOrCreateCart($userId, $sessionId);

        // 加入購物車
        $cartItem = $this->cartRepository->addItem(
            $cart->id,
            $productId,
            $quantity,
            $price
        );

        return $this->cartRepository->getCartSummary($cart->id);
    }

    /**
     * 更新購物車項目數量
     */
    public function updateCartItem($cartItemId, $quantity)
    {
        if ($quantity < 0) {
            throw new \Exception('數量不能為負數');
        }

        // 驗證購物車項目屬於當前使用者
        $this->validateCartItemOwnership($cartItemId);

        $cartItem = $this->cartRepository->updateItemQuantity($cartItemId, $quantity);

        // 取得更新後的購物車摘要
        $userId = Auth::id();
        $sessionId = Session::getId();
        $cart = $this->cartRepository->getOrCreateCart($userId, $sessionId);

        return $this->cartRepository->getCartSummary($cart->id);
    }

    /**
     * 移除購物車項目
     */
    public function removeCartItem($cartItemId)
    {
        // 驗證購物車項目屬於當前使用者
        $this->validateCartItemOwnership($cartItemId);

        $this->cartRepository->removeItem($cartItemId);

        // 取得更新後的購物車摘要
        $userId = Auth::id();
        $sessionId = Session::getId();
        $cart = $this->cartRepository->getOrCreateCart($userId, $sessionId);

        return $this->cartRepository->getCartSummary($cart->id);
    }

    /**
     * 清空購物車
     */
    public function clearCart()
    {
        $userId = Auth::id();
        $sessionId = Session::getId();
        $cart = $this->cartRepository->getOrCreateCart($userId, $sessionId);

        $this->cartRepository->clearCart($cart->id);

        return [
            'cart_id' => $cart->id,
            'items' => [],
            'total_quantity' => 0,
            'total_price' => 0,
            'item_count' => 0
        ];
    }

    /**
     * 合併訪客購物車（登入時使用）
     */
    public function mergeGuestCart($guestSessionId)
    {
        $userId = Auth::id();

        if (!$userId) {
            throw new \Exception('使用者未登入');
        }

        // 取得訪客購物車
        $guestCart = $this->cartRepository->getCartBySession($guestSessionId);

        if (!$guestCart) {
            return $this->getCurrentCart();
        }

        // 取得或建立使用者購物車
        $userCart = $this->cartRepository->getOrCreateCart($userId);

        // 合併購物車
        $this->cartRepository->mergeCarts($guestCart->id, $userCart->id);

        return $this->cartRepository->getCartSummary($userCart->id);
    }

    /**
     * 驗證購物車項目是否屬於當前使用者
     */
    protected function validateCartItemOwnership($cartItemId)
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        $cartItem = \App\Models\Cart\CartItem::with('cart')->findOrFail($cartItemId);

        // 檢查購物車是否屬於當前使用者或 session
        if ($userId) {
            if ($cartItem->cart->user_id != $userId) {
                throw new \Exception('無權操作此購物車項目');
            }
        } else {
            if ($cartItem->cart->session_id != $sessionId) {
                throw new \Exception('無權操作此購物車項目');
            }
        }

        return true;
    }

    /**
     * 驗證購物車庫存（結帳前使用）
     */
    public function validateCartStock()
    {
        $userId = Auth::id();
        $sessionId = Session::getId();
        $cart = $this->cartRepository->getOrCreateCart($userId, $sessionId);

        $cartItems = $this->cartRepository->getCartItems($cart->id);
        $errors = [];

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (!$product->is_active) {
                $errors[] = "{$item->display_name} 目前無法購買";
                continue;
            }

            if ($product->stock < $item->quantity) {
                $errors[] = "{$item->display_name} 庫存不足（剩餘 {$product->stock} 件）";
            }
        }

        if (!empty($errors)) {
            throw new \Exception(implode('、', $errors));
        }

        return true;
    }
}
