<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', '請先登入後再結帳');
        }

        try {
            $checkoutData = $this->orderService->getCheckoutData();
            return view('checkout.index', [
                'checkoutData' => $checkoutData
            ]);
        } catch (\Exception $e) {
            return redirect('/')->with('error', $e->getMessage());
        }
    }

    public function getCheckoutData()
    {
        try {
            $data = $this->orderService->getCheckoutData();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_city' => 'required|string|max:100',
            'shipping_district' => 'required|string|max:100',
            'shipping_address' => 'required|string|max:500',
            'payment_method' => 'required|in:credit_card,cash_on_delivery',
            'note' => 'nullable|string|max:1000',
        ], [
            'shipping_name.required' => '請輸入收件人姓名',
            'shipping_phone.required' => '請輸入收件人電話',
            'shipping_city.required' => '請選擇城市',
            'shipping_district.required' => '請選擇區域',
            'shipping_address.required' => '請輸入詳細地址',
            'payment_method.required' => '請選擇付款方式',
            'payment_method.in' => '無效的付款方式',
        ]);

        try {
            $shippingData = [
                'name' => $validated['shipping_name'],
                'phone' => $validated['shipping_phone'],
                'city' => $validated['shipping_city'],
                'district' => $validated['shipping_district'],
                'address' => $validated['shipping_address'],
            ];

            $order = $this->orderService->createOrder(
                $shippingData,
                $validated['payment_method'],
                $validated['note'] ?? null
            );

            // 信用卡付款導向 ECPay
            if ($validated['payment_method'] === 'credit_card') {
                return response()->json([
                    'success' => true,
                    'message' => '訂單建立成功，導向付款頁面',
                    'data' => [
                        'order_number' => $order->order_number,
                        'total' => $order->total,
                        'redirect_url' => route('ecpay.checkout') . '?order_number=' . $order->order_number
                    ]
                ], 201);
            }

            // 貨到付款直接顯示成功頁面
            return response()->json([
                'success' => true,
                'message' => '訂單建立成功',
                'data' => [
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'redirect_url' => route('checkout.success', ['orderNumber' => $order->order_number])
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function success($orderNumber)
    {
        try {
            $order = $this->orderService->getOrderDetail($orderNumber);

            return view('checkout.success', [
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return redirect('/')->with('error', $e->getMessage());
        }
    }

    public function orders()
    {
        try {
            $orders = $this->orderService->getOrdersByUser();

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function orderDetail($orderNumber)
    {
        try {
            $order = $this->orderService->getOrderDetail($orderNumber);

            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
