<?php

namespace App\Repositories\Cart;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Repositories\Contracts\Cart\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    protected $cartModel;
    protected $cartItemModel;

    public function __construct(Cart $cartModel, CartItem $cartItemModel)
    {
        $this->cartModel = $cartModel;
        $this->cartItemModel = $cartItemModel;
    }

    /**
     * 根據使用者 ID 取得購物車
     */
    public function getCartByUser($userId)
    {
        return $this->cartModel->where('user_id', $userId)->first();
    }

    /**
     * 根據 session ID 取得購物車
     */
    public function getCartBySession($sessionId)
    {
        return $this->cartModel->where('session_id', $sessionId)->first();
    }

    /**
     * 取得或建立購物車
     */
    public function getOrCreateCart($userId = null, $sessionId = null)
    {
        if ($userId) {
            $cart = $this->getCartByUser($userId);
            if (!$cart) {
                $cart = $this->cartModel->create(['user_id' => $userId]);
            }
        } elseif ($sessionId) {
            $cart = $this->getCartBySession($sessionId);
            if (!$cart) {
                $cart = $this->cartModel->create(['session_id' => $sessionId]);
            }
        } else {
            throw new \Exception('必須提供 userId 或 sessionId');
        }

        return $cart;
    }

    /**
     * 新增商品到購物車
     */
    public function addItem($cartId, $productId, $quantity, $price)
    {
        // 檢查是否已存在相同商品
        $existingItem = $this->findCartItem($cartId, $productId);

        if ($existingItem) {
            // 如果已存在，增加數量
            $existingItem->quantity += $quantity;
            $existingItem->save();
            return $existingItem;
        }

        // 建立新的購物車項目
        return $this->cartItemModel->create([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price
        ]);
    }

    /**
     * 更新購物車項目數量
     */
    public function updateItemQuantity($cartItemId, $quantity)
    {
        $cartItem = $this->cartItemModel->findOrFail($cartItemId);

        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

        return $cartItem;
    }

    /**
     * 移除購物車項目
     */
    public function removeItem($cartItemId)
    {
        $cartItem = $this->cartItemModel->findOrFail($cartItemId);
        return $cartItem->delete();
    }

    /**
     * 清空購物車
     */
    public function clearCart($cartId)
    {
        return $this->cartItemModel->where('cart_id', $cartId)->delete();
    }

    /**
     * 取得購物車所有項目
     */
    public function getCartItems($cartId)
    {
        return $this->cartItemModel
            ->where('cart_id', $cartId)
            ->with(['product'])
            ->get();
    }

    /**
     * 尋找特定的購物車項目
     */
    public function findCartItem($cartId, $productId)
    {
        return $this->cartItemModel
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }

    /**
     * 取得購物車摘要資訊
     */
    public function getCartSummary($cartId)
    {
        $cart = $this->cartModel->with('items')->findOrFail($cartId);

        $items = $this->getCartItems($cartId);

        $totalQuantity = $items->sum('quantity');
        $totalPrice = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return [
            'cart_id' => $cart->id,
            'items' => $items,
            'total_quantity' => $totalQuantity,
            'total_price' => $totalPrice,
            'item_count' => $items->count()
        ];
    }

    /**
     * 合併購物車（訪客登入後）
     */
    public function mergeCarts($guestCartId, $userCartId)
    {
        DB::beginTransaction();

        try {
            $guestCart = $this->cartModel->findOrFail($guestCartId);
            $userCart = $this->cartModel->findOrFail($userCartId);

            $guestItems = $this->getCartItems($guestCartId);

            foreach ($guestItems as $guestItem) {
                // 檢查使用者購物車是否已有相同商品
                $existingItem = $this->findCartItem(
                    $userCartId,
                    $guestItem->product_id
                );

                if ($existingItem) {
                    // 如果已存在，合併數量
                    $existingItem->quantity += $guestItem->quantity;
                    $existingItem->save();
                } else {
                    // 如果不存在，將訪客的項目轉移到使用者購物車
                    $guestItem->cart_id = $userCartId;
                    $guestItem->save();
                }
            }

            // 刪除訪客購物車
            $guestCart->delete();

            DB::commit();

            return $userCart;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
