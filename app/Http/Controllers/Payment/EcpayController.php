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
     */
    public function callback(Request $request)
    {
        $result = $this->ecpayService->handlePaymentCallback($request->all());

        // 準備訂單資料
        $order = $result['order'] ?? null;
        $payment = $result['payment'] ?? null;

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

        if ($result['success']) {
            return Inertia::render('Ecpay/Success', [
                'order_number' => $result['order_number'],
                'message' => $result['message'],
                'order' => $orderData,
                'payment' => $paymentData,
            ]);
        }

        return Inertia::render('Ecpay/Pending', [
            'order_number' => $result['order_number'],
            'message' => $result['message'],
            'order' => $orderData,
            'payment' => $paymentData,
        ]);
        return view('ecpay.success');
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
