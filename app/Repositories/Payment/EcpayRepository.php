<?php

namespace App\Repositories\Payment;

use App\Models\Order\Order;
use App\Models\Payment\Payment;
use App\Repositories\Contracts\Payment\EcpayRepositoryInterface;

class EcpayRepository implements EcpayRepositoryInterface
{
    protected $paymentModel;
    protected $orderModel;

    public function __construct(Payment $paymentModel, Order $orderModel)
    {
        $this->paymentModel = $paymentModel;
        $this->orderModel = $orderModel;
    }

    /**
     * 建立付款記錄
     */
    public function createPayment(array $data)
    {
        return $this->paymentModel->create($data);
    }

    /**
     * 根據交易編號查詢付款記錄
     */
    public function findByTradeNo(string $tradeNo)
    {
        return $this->paymentModel->where('trade_no', $tradeNo)->first();
    }

    /**
     * 根據訂單 ID 查詢付款記錄
     */
    public function findByOrderId(int $orderId)
    {
        return $this->paymentModel->where('order_id', $orderId)->first();
    }

    /**
     * 更新付款狀態
     */
    public function updatePaymentStatus(string $tradeNo, string $status, array $responseData = [])
    {
        $payment = $this->findByTradeNo($tradeNo);

        if (!$payment) {
            return null;
        }

        $updateData = ['status' => $status];

        if (!empty($responseData)) {
            $updateData['response_data'] = $responseData;

            if (isset($responseData['TradeNo'])) {
                $updateData['ecpay_trade_no'] = $responseData['TradeNo'];
            }

            if (isset($responseData['PaymentType'])) {
                $updateData['payment_method'] = $responseData['PaymentType'];
            }

            if ($status === Payment::STATUS_PAID) {
                $updateData['payment_date'] = now();
            }
        }

        $payment->update($updateData);

        return $payment->fresh();
    }

    /**
     * 根據訂單編號查詢訂單
     */
    public function findOrderByNumber(string $orderNumber)
    {
        return $this->orderModel->where('order_number', $orderNumber)->first();
    }

    /**
     * 更新訂單付款狀態
     */
    public function updateOrderPaymentStatus(int $orderId, string $status)
    {
        $order = $this->orderModel->find($orderId);

        if (!$order) {
            return null;
        }

        $order->status = $status;

        if ($status === 'paid') {
            $order->paid_at = now();
        }

        $order->save();

        return $order;
    }
}
