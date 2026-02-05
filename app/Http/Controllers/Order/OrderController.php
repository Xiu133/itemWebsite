<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * 顯示訂單列表
     */
    public function index()
    {
        try {
            $orders = $this->orderService->getOrdersByUser();

            return view('orders.index', [
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            return redirect('/')->with('error', $e->getMessage());
        }
    }

    /**
     * 顯示訂單詳情
     */
    public function show($orderNumber)
    {
        try {
            $order = $this->orderService->getOrderDetail($orderNumber);

            return view('orders.show', [
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return redirect()->route('orders.index')->with('error', $e->getMessage());
        }
    }
}
