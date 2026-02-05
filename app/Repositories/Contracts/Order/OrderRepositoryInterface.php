<?php

namespace App\Repositories\Contracts\Order;

interface OrderRepositoryInterface
{
    public function create(array $data);

    public function createOrderItem($orderId, array $itemData);

    public function findById($id);

    public function findByOrderNumber($orderNumber);

    public function getOrdersByUser($userId);

    public function updateStatus($orderId, $status);

    public function getOrderWithItems($orderId);
}
