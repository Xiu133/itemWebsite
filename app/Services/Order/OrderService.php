<?php

namespace App\Services\Order;

use App\Repositories\Contracts\Order\OrderRepositoryInterface;
use App\Repositories\Contracts\Cart\CartRepositoryInterface;
use App\Services\Cart\CartService;
use App\Models\Order\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Spatie\Activitylog\Facades\Activity;

class OrderService
{
    protected $orderRepository;
    protected $cartRepository;
    protected $cartService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository,
        CartService $cartService
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
        $this->cartService = $cartService;
    }

    public function createOrder(array $shippingData, $paymentMethod, $note = null)
    {
        $userId = Auth::id();

        if (!$userId) {
            throw new \Exception('請先登入');
        }

        // 取得購物車 ID
        $cart = $this->cartRepository->getCartByUser($userId);

        if (!$cart) {
            throw new \Exception('購物車不存在');
        }

        // 生成訂單編號
        $orderNumber = Order::generateOrderNumber();

        try {
            // 使用 Stored Procedure 創建訂單
            // SP 會處理：驗證庫存、建立訂單、建立訂單項目、扣減庫存、清空購物車
            $result = DB::selectOne("
                SELECT * FROM sp_create_order(
                    :user_id,
                    :cart_id,
                    :order_number,
                    :shipping_name,
                    :shipping_phone,
                    :shipping_city,
                    :shipping_district,
                    :shipping_address,
                    :payment_method,
                    :note
                )
            ", [
                'user_id' => $userId,
                'cart_id' => $cart->id,
                'order_number' => $orderNumber,
                'shipping_name' => $shippingData['name'],
                'shipping_phone' => $shippingData['phone'],
                'shipping_city' => $shippingData['city'],
                'shipping_district' => $shippingData['district'],
                'shipping_address' => $shippingData['address'],
                'payment_method' => $paymentMethod,
                'note' => $note,
            ]);

            // 取得完整訂單資料
            $order = $this->orderRepository->getOrderWithItems($result->order_id);

            // 記錄訂單建立日誌
            activity('order')
                ->performedOn($order)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'create',
                    'order_number' => $result->order_number,
                    'total' => $result->total,
                    'items_count' => $result->items_count,
                    'payment_method' => $paymentMethod,
                ])
                ->log("建立訂單: {$result->order_number}");

            return $order;

        } catch (\Exception $e) {
            // PostgreSQL 錯誤訊息轉換為友善訊息
            $message = $e->getMessage();

            if (str_contains($message, '庫存不足')) {
                // 提取庫存不足的錯誤訊息
                preg_match('/庫存不足.*/', $message, $matches);
                throw new \Exception($matches[0] ?? '庫存不足，無法完成訂單');
            }

            if (str_contains($message, '購物車是空的')) {
                throw new \Exception('購物車是空的');
            }

            if (str_contains($message, '購物車不存在')) {
                throw new \Exception('購物車不存在或不屬於該用戶');
            }

            throw $e;
        }
    }

    protected function calculateShippingFee($subtotal)
    {
        // 滿 1000 免運費
        if ($subtotal >= 1000) {
            return 0;
        }
        return 60; // 運費 60 元
    }

    public function getOrdersByUser()
    {
        $userId = Auth::id();

        if (!$userId) {
            throw new \Exception('請先登入');
        }

        return $this->orderRepository->getOrdersByUser($userId);
    }

    public function getOrderDetail($orderNumber)
    {
        $userId = Auth::id();

        if (!$userId) {
            throw new \Exception('請先登入');
        }

        $order = $this->orderRepository->findByOrderNumber($orderNumber);

        if (!$order) {
            throw new \Exception('找不到此訂單');
        }

        if ($order->user_id !== $userId) {
            throw new \Exception('無權查看此訂單');
        }

        return $this->orderRepository->getOrderWithItems($order->id);
    }

    public function getCheckoutData()
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        $cart = $this->cartRepository->getOrCreateCart($userId, $sessionId);
        $cartSummary = $this->cartRepository->getCartSummary($cart->id);

        if (empty($cartSummary['items']) || $cartSummary['items']->isEmpty()) {
            throw new \Exception('購物車是空的');
        }

        $subtotal = $cartSummary['total_price'];
        $shippingFee = $this->calculateShippingFee($subtotal);
        $total = $subtotal + $shippingFee;

        return [
            'items' => $cartSummary['items']->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->display_name,
                    'image' => $item->image_url,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'summary' => [
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount' => 0,
                'total' => $total,
                'free_shipping_threshold' => 1000,
                'amount_to_free_shipping' => max(0, 1000 - $subtotal),
            ],
        ];
    }
}
