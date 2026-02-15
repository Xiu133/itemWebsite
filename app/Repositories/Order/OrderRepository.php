<?php

namespace App\Repositories\Order;

use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Repositories\Contracts\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    protected $orderModel;
    protected $orderItemModel;

    public function __construct(Order $orderModel, OrderItem $orderItemModel)
    {
        $this->orderModel = $orderModel;
        $this->orderItemModel = $orderItemModel;
    }

    public function create(array $data)
    {
        return $this->orderModel->create($data);
    }

    public function createOrderItem($orderId, array $itemData)
    {
        $itemData['order_id'] = $orderId;
        return $this->orderItemModel->create($itemData);
    }

    public function findById($id)
    {
        return $this->orderModel->find($id);
    }

    public function findByOrderNumber($orderNumber)
    {
        return $this->orderModel->where('order_number', $orderNumber)->first();
    }

    public function getOrdersByUser($userId)
    {
        return $this->orderModel
            ->with('items.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus($orderId, $status)
    {
        $order = $this->findById($orderId);
        if ($order) {
            $order->status = $status;
            if ($status === 'paid') {
                $order->paid_at = now();
            }
            $order->save();
        }
        return $order;
    }

    public function getOrderWithItems($orderId)
    {
        return $this->orderModel->with('items')->find($orderId);
    }
}
