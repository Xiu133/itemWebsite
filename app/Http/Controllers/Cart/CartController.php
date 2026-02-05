<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * 取得購物車內容
     * GET /api/cart
     */
    public function index()
    {
        try {
            $cart = $this->cartService->getCurrentCart();

            return response()->json([
                'success' => true,
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 加入商品到購物車
     * POST /api/cart
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ], [
            'product_id.required' => '請選擇商品',
            'product_id.exists' => '商品不存在',
            'quantity.required' => '請輸入數量',
            'quantity.min' => '數量至少為 1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $cart = $this->cartService->addToCart(
                $request->product_id,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'message' => '已加入購物車',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 更新購物車項目數量
     * PUT /api/cart/{cartItemId}
     */
    public function update(Request $request, $cartItemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0'
        ], [
            'quantity.required' => '請輸入數量',
            'quantity.min' => '數量不能為負數'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $cart = $this->cartService->updateCartItem(
                $cartItemId,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'message' => '已更新數量',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 移除購物車項目
     * DELETE /api/cart/{cartItemId}
     */
    public function destroy($cartItemId)
    {
        try {
            $cart = $this->cartService->removeCartItem($cartItemId);

            return response()->json([
                'success' => true,
                'message' => '已移除商品',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 清空購物車
     * DELETE /api/cart
     */
    public function clear()
    {
        try {
            $cart = $this->cartService->clearCart();

            return response()->json([
                'success' => true,
                'message' => '已清空購物車',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 驗證購物車庫存（結帳前使用）
     * POST /api/cart/validate
     */
    public function validate()
    {
        try {
            $this->cartService->validateCartStock();

            return response()->json([
                'success' => true,
                'message' => '購物車驗證通過'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 同步前端購物車到資料庫（結帳前使用）
     * POST /api/cart/sync
     */
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ], [
            'items.required' => '購物車是空的',
            'items.*.product_id.required' => '商品 ID 必填',
            'items.*.product_id.exists' => '商品不存在',
            'items.*.quantity.required' => '數量必填',
            'items.*.quantity.min' => '數量至少為 1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // 先清空現有購物車
            $this->cartService->clearCart();

            // 逐一加入商品
            foreach ($request->items as $item) {
                $this->cartService->addToCart(
                    $item['product_id'],
                    $item['quantity']
                );
            }

            $cart = $this->cartService->getCurrentCart();

            return response()->json([
                'success' => true,
                'message' => '購物車已同步',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 合併訪客購物車（登入時使用）
     * POST /api/cart/merge
     */
    public function merge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_session_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $cart = $this->cartService->mergeGuestCart($request->guest_session_id);

            return response()->json([
                'success' => true,
                'message' => '購物車已合併',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
