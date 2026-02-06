<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\EcpayService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EcpayController extends Controller
{
    protected $ecpayService;

    public function __construct(EcpayService $ecpayService)
    {
        $this->ecpayService = $ecpayService;
    }

    /**
     * 建立訂單並導向到綠界付款
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        try {
            $formHtml = $this->ecpayService->createPaymentForm(0, [
                'order_number' => $request->input('order_number'),
                'payment_method' => 'Credit',
                'trade_desc' => '商品訂單',
            ]);

            return response($formHtml);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 綠界付款結果通知（Server 端）
     * 這是綠界主動呼叫的 API，用於通知付款結果
     */
    public function notify(Request $request)
    {
        $result = $this->ecpayService->handlePaymentNotify($request->all());

        // 綠界規定回傳格式
        return $result['success'] ? '1|OK' : '0|FAIL';
    }

    /**
     * 付款完成回調頁面（Client 端）
     * 使用者付款完成後，綠界會將使用者導向此頁面
     *
     * 注意：由於 SameSite=Lax cookie 政策，跨站 POST 請求不會帶 session cookie
     * 此路由已排除 session 中間件，避免創建新 session 導致用戶登出
     * 使用 JavaScript 重定向讓瀏覽器發起全新的 GET 請求，帶上正確的 session cookie
     */
    public function callback(Request $request)
    {
        $result = $this->ecpayService->handlePaymentCallback($request->all());

        $orderNumber = $result['order_number'] ?? '';
        $status = $result['success'] ? 'success' : 'pending';
        $message = $status === 'success' ? '付款成功，正在跳轉...' : '付款處理中，正在跳轉...';

        $redirectUrl = route('ecpay.result', [
            'order_number' => $orderNumber,
            'status' => $status,
        ]);

        // 直接返回 HTML，不使用 Blade 視圖（因為此路由已排除 session 中間件）
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>付款處理中 — MONO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Noto Sans TC', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .container { text-align: center; padding: 2rem; }
        .spinner {
            width: 50px; height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top-color: #10b981;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .message { font-size: 1.25rem; color: rgba(255, 255, 255, 0.9); margin-bottom: 0.5rem; }
        .hint { font-size: 0.875rem; color: rgba(255, 255, 255, 0.5); }
        .manual-link { margin-top: 2rem; font-size: 0.875rem; color: rgba(255, 255, 255, 0.5); }
        .manual-link a { color: #10b981; text-decoration: none; }
        .manual-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <p class="message">{$message}</p>
        <p class="hint">請稍候，系統正在處理您的請求</p>
        <p class="manual-link">如果頁面沒有自動跳轉，請<a href="{$redirectUrl}">點擊這裡</a></p>
    </div>
    <script>window.location.replace("{$redirectUrl}");</script>
</body>
</html>
HTML;

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * 付款結果頁面（GET 請求）
     * 從 callback 重定向過來，此時 session 會被正確保留
     */
    public function result(Request $request)
    {
        $orderNumber = $request->query('order_number');
        $status = $request->query('status', 'pending');

        // 查詢訂單和付款資料
        $order = \App\Models\Order\Order::where('order_number', $orderNumber)->first();
        $payment = $order ? $order->payment : null;

        $orderData = $order ? [
            'order_number' => $order->order_number,
            'total' => $order->total,
            'status' => $order->status,
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            'items' => $order->items->map(fn($item) => [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->quantity * $item->price,
            ])->toArray(),
        ] : null;

        $paymentData = $payment ? [
            'trade_no' => $payment->trade_no,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
        ] : null;

        // 取得當前用戶 ID（用於清除購物車）
        $userId = auth()->id();

        if ($status === 'success') {
            return Inertia::render('Ecpay/Success', [
                'order_number' => $orderNumber,
                'message' => '付款成功！感謝您的購買。',
                'order' => $orderData,
                'payment' => $paymentData,
                'user_id' => $userId,
            ]);
        }

        return Inertia::render('Ecpay/Pending', [
            'order_number' => $orderNumber,
            'message' => '付款處理中，請稍候...',
            'order' => $orderData,
            'payment' => $paymentData,
            'user_id' => $userId,
        ]);
    }

    /**
     * 付款完成返回頁面（Client 端）
     * 使用者點擊「返回商店」按鈕時導向此頁面
     */
    public function return(Request $request)
    {
        return Inertia::render('Ecpay/Return', [
            'message' => '感謝您的購買！',
        ]);
    }

    /**
     * 查詢付款狀態
     */
    public function queryStatus(Request $request)
    {
        $request->validate([
            'trade_no' => 'required|string',
        ]);

        $result = $this->ecpayService->queryPaymentStatus($request->input('trade_no'));

        return response()->json($result);
    }
}
