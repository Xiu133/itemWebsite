<?php

namespace App\Services\Payment;

use App\Models\Payment\Payment;
use App\Repositories\Contracts\Payment\EcpayRepositoryInterface;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Services\CheckMacValueService;
use Ecpay\Sdk\Services\UrlService;
use Illuminate\Support\Facades\Log;

class EcpayService
{
    protected $ecpayRepository;
    protected $factory;

    // 綠界環境參數（建議之後移到 config 或 .env）
    protected $hashKey;
    protected $hashIv;
    protected $merchantId;
    protected $isProduction;

    public function __construct(EcpayRepositoryInterface $ecpayRepository)
    {
        $this->ecpayRepository = $ecpayRepository;

        // 從環境變數讀取設定
        $this->hashKey = config('ecpay.hash_key', '5294y06JbISpM5x9');
        $this->hashIv = config('ecpay.hash_iv', 'v77hoKGq4kWxNNIS');
        $this->merchantId = config('ecpay.merchant_id', '2000132');
        $this->isProduction = config('ecpay.production', false);

        $this->factory = new Factory([
            'hashKey' => $this->hashKey,
            'hashIv' => $this->hashIv,
        ]);
    }

    /**
     * 取得綠界付款網址
     */
    protected function getPaymentUrl(): string
    {
        return $this->isProduction
            ? 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5'
            : 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';
    }

    /**
     * 產生交易編號
     */
    public function generateTradeNo(): string
    {
        // 綠界規定：MerchantTradeNo 長度最多 20 字元
        return 'EC' . date('YmdHis') . substr(uniqid(), -4);
    }

    /**
     * 建立綠界付款表單
     */
    public function createPaymentForm(int $orderId, array $options = []): string
    {
        $order = $this->ecpayRepository->findOrderByNumber($options['order_number'] ?? '');

        if (!$order) {
            throw new \Exception('找不到訂單');
        }

        // 產生交易編號
        $tradeNo = $this->generateTradeNo();

        // 建立付款記錄
        $this->ecpayRepository->createPayment([
            'order_id' => $order->id,
            'trade_no' => $tradeNo,
            'amount' => $order->total,
            'status' => Payment::STATUS_PENDING,
        ]);

        // 準備商品名稱
        $itemName = $this->prepareItemName($order);

        $autoSubmitFormService = $this->factory->create('AutoSubmitFormWithCmvService');

        $input = [
            'MerchantID' => $this->merchantId,
            'MerchantTradeNo' => $tradeNo,
            'MerchantTradeDate' => date('Y/m/d H:i:s'),
            'PaymentType' => 'aio',
            'TotalAmount' => (int) $order->total,
            'TradeDesc' => UrlService::ecpayUrlEncode($options['trade_desc'] ?? '商品訂單'),
            'ItemName' => $itemName,
            'ReturnURL' => route('ecpay.notify'),
            'OrderResultURL' => route('ecpay.callback'),
            'ClientBackURL' => route('ecpay.return'),
            'ChoosePayment' => $options['payment_method'] ?? 'ALL',
            'EncryptType' => 1,
            'CustomField1' => $order->order_number, // 儲存原始訂單編號
        ];

        // 信用卡分期設定
        if (isset($options['installment']) && $options['installment'] > 0) {
            $input['CreditInstallment'] = $options['installment'];
        }

        return $autoSubmitFormService->generate($input, $this->getPaymentUrl());
    }

    /**
     * 準備商品名稱（綠界規定格式）
     */
    protected function prepareItemName($order): string
    {
        $items = $order->items ?? collect();

        if ($items->isEmpty()) {
            return '訂單商品';
        }

        // 綠界規定：多項商品用 # 分隔，總長度最多 400 字元
        $itemNames = $items->map(function ($item) {
            return $item->product_name . ' x ' . $item->quantity;
        })->toArray();

        $itemName = implode('#', $itemNames);

        // 如果超過 400 字元，截斷並加上省略號
        if (mb_strlen($itemName) > 397) {
            $itemName = mb_substr($itemName, 0, 397) . '...';
        }

        return $itemName;
    }

    /**
     * 驗證綠界回傳的 CheckMacValue
     */
    public function verifyCheckMacValue(array $data): bool
    {
        $checkMacValueService = $this->factory->create(CheckMacValueService::class);

        // verify 方法需要傳入包含 CheckMacValue 的完整陣列
        return $checkMacValueService->verify($data);
    }

    /**
     * 處理付款通知（Server 端）
     */
    public function handlePaymentNotify(array $data): array
    {
        Log::info('綠界付款通知', $data);

        // 驗證 CheckMacValue
        if (!$this->verifyCheckMacValue($data)) {
            Log::error('綠界 CheckMacValue 驗證失敗', $data);
            return [
                'success' => false,
                'message' => 'CheckMacValue 驗證失敗',
            ];
        }

        $tradeNo = $data['MerchantTradeNo'] ?? '';
        $rtnCode = $data['RtnCode'] ?? '';

        // 查詢付款記錄
        $payment = $this->ecpayRepository->findByTradeNo($tradeNo);

        if (!$payment) {
            Log::error('找不到付款記錄', ['trade_no' => $tradeNo]);
            return [
                'success' => false,
                'message' => '找不到付款記錄',
            ];
        }

        // 判斷付款結果
        if ($rtnCode == '1') {
            // 付款成功
            $this->ecpayRepository->updatePaymentStatus(
                $tradeNo,
                Payment::STATUS_PAID,
                $data
            );

            // 更新訂單狀態
            $this->ecpayRepository->updateOrderPaymentStatus($payment->order_id, 'paid');

            Log::info('綠界付款成功', [
                'trade_no' => $tradeNo,
                'order_id' => $payment->order_id,
            ]);

            return [
                'success' => true,
                'message' => '付款成功',
                'payment' => $payment->fresh(),
            ];
        } else {
            // 付款失敗
            $this->ecpayRepository->updatePaymentStatus(
                $tradeNo,
                Payment::STATUS_FAILED,
                $data
            );

            Log::warning('綠界付款失敗', [
                'trade_no' => $tradeNo,
                'rtn_code' => $rtnCode,
                'rtn_msg' => $data['RtnMsg'] ?? '',
            ]);

            return [
                'success' => false,
                'message' => $data['RtnMsg'] ?? '付款失敗',
            ];
        }
    }

    /**
     * 處理付款完成回調（Client 端）
     * 同時處理付款狀態更新，解決本地開發環境 notify 無法回調的問題
     */
    public function handlePaymentCallback(array $data): array
    {
        $tradeNo = $data['MerchantTradeNo'] ?? '';
        $orderNumber = $data['CustomField1'] ?? '';
        $rtnCode = $data['RtnCode'] ?? '';

        Log::info('綠界 Callback 資料', $data);

        $payment = $this->ecpayRepository->findByTradeNo($tradeNo);

        if (!$payment) {
            return [
                'success' => false,
                'message' => '找不到付款記錄',
                'order_number' => $orderNumber,
            ];
        }

        // 如果付款狀態還是 pending，且綠界回傳成功，則更新狀態
        // 這樣可以解決本地環境 notify 無法回調的問題
        if ($payment->status === Payment::STATUS_PENDING && $rtnCode == '1') {
            // 驗證 CheckMacValue
            if ($this->verifyCheckMacValue($data)) {
                $this->ecpayRepository->updatePaymentStatus(
                    $tradeNo,
                    Payment::STATUS_PAID,
                    $data
                );

                // 更新訂單狀態
                $this->ecpayRepository->updateOrderPaymentStatus($payment->order_id, 'paid');

                Log::info('透過 Callback 更新付款狀態為成功', [
                    'trade_no' => $tradeNo,
                    'order_id' => $payment->order_id,
                ]);

                // 重新載入付款記錄
                $payment = $payment->fresh();
            }
        }

        $order = $payment->order;

        return [
            'success' => $payment->status === Payment::STATUS_PAID,
            'message' => $payment->status === Payment::STATUS_PAID ? '付款成功' : '付款處理中',
            'order_number' => $order->order_number ?? $orderNumber,
            'payment' => $payment,
            'order' => $order,
        ];
    }

    /**
     * 查詢訂單付款狀態
     */
    public function queryPaymentStatus(string $tradeNo): array
    {
        $payment = $this->ecpayRepository->findByTradeNo($tradeNo);

        if (!$payment) {
            return [
                'success' => false,
                'message' => '找不到付款記錄',
            ];
        }

        return [
            'success' => true,
            'payment' => $payment,
            'order' => $payment->order,
        ];
    }

    /**
     * 根據訂單 ID 取得付款記錄
     */
    public function getPaymentByOrderId(int $orderId)
    {
        return $this->ecpayRepository->findByOrderId($orderId);
    }
}
