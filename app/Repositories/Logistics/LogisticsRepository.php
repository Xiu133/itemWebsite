<?php

namespace App\Repositories\Logistics;

use App\Models\Order\Order;
use App\Repositories\Contracts\Logistics\LogisticsRepositoryInterface;

class LogisticsRepository implements LogisticsRepositoryInterface
{
    protected $orderModel;

    public function __construct(Order $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    /**
     * 根據訂單 ID 查詢訂單
     */
    public function findOrderById(int $orderId)
    {
        return $this->orderModel->with('items')->find($orderId);
    }

    /**
     * 根據物流交易編號查詢訂單
     */
    public function findOrderByLogisticsTradeNo(string $tradeNo)
    {
        return $this->orderModel->where('logistics_trade_no', $tradeNo)->first();
    }

    /**
     * 更新訂單物流資訊
     */
    public function updateOrderLogistics(int $orderId, array $data)
    {
        $order = $this->findOrderById($orderId);

        if (!$order) {
            return null;
        }

        $order->update($data);

        return $order->fresh();
    }

    /**
     * 更新訂單物流狀態
     */
    public function updateLogisticsStatus(int $orderId, string $status)
    {
        $order = $this->findOrderById($orderId);

        if (!$order) {
            return null;
        }

        $updateData = ['logistics_status' => $status];

        // 如果狀態為已送達，更新訂單狀態為已完成
        if ($status === Order::LOGISTICS_STATUS_DELIVERED) {
            $updateData['status'] = 'completed';
        }

        // 如果狀態為運送中，更新出貨時間和訂單狀態
        if ($status === Order::LOGISTICS_STATUS_IN_TRANSIT && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
            $updateData['status'] = 'shipped';
        }

        $order->update($updateData);

        return $order->fresh();
    }
}
