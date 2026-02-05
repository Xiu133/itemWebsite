<?php

namespace App\Repositories\Contracts\Logistics;

interface LogisticsRepositoryInterface
{
    /**
     * 根據訂單 ID 查詢訂單
     */
    public function findOrderById(int $orderId);

    /**
     * 根據物流交易編號查詢訂單
     */
    public function findOrderByLogisticsTradeNo(string $tradeNo);

    /**
     * 更新訂單物流資訊
     */
    public function updateOrderLogistics(int $orderId, array $data);

    /**
     * 更新訂單物流狀態
     */
    public function updateLogisticsStatus(int $orderId, string $status);
}
