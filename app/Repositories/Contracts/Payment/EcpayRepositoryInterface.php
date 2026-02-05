<?php

namespace App\Repositories\Contracts\Payment;

interface EcpayRepositoryInterface
{
    /**
     * 建立付款記錄
     */
    public function createPayment(array $data);

    /**
     * 根據交易編號查詢付款記錄
     */
    public function findByTradeNo(string $tradeNo);

    /**
     * 根據訂單 ID 查詢付款記錄
     */
    public function findByOrderId(int $orderId);

    /**
     * 更新付款狀態
     */
    public function updatePaymentStatus(string $tradeNo, string $status, array $responseData = []);

    /**
     * 根據訂單編號查詢訂單
     */
    public function findOrderByNumber(string $orderNumber);

    /**
     * 更新訂單付款狀態
     */
    public function updateOrderPaymentStatus(int $orderId, string $status);
}
