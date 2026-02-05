<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderManageService;
use Illuminate\Http\Request;

class OrderManageController extends Controller
{
    protected $service;

    public function __construct(OrderManageService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $orders = $this->service->getAllOrders($status);
        $statistics = $this->service->getStatistics();

        return view('orders.manage.index', compact('orders', 'statistics', 'status'));
    }

    public function show(int $id)
    {
        $order = $this->service->getOrderById($id);

        return view('orders.manage.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,completed,cancelled',
        ]);

        $this->service->updateStatus($id, $request->input('status'));

        return response()->json(['success' => true]);
    }

    public function addNote(Request $request, int $id)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $this->service->addNote($id, $request->input('note'));

        return redirect()->back()->with('success', '備註已更新');
    }
}
