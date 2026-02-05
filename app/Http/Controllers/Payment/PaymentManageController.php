<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentManageService;
use Illuminate\Http\Request;

class PaymentManageController extends Controller
{
    protected $service;

    public function __construct(PaymentManageService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $payments = $this->service->getAllPayments($status);
        $statistics = $this->service->getStatistics();

        return view('payments.manage.index', compact('payments', 'statistics', 'status'));
    }

    public function show(int $id)
    {
        $payment = $this->service->getPaymentById($id);

        return view('payments.manage.show', compact('payment'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $this->service->updateStatus($id, $request->input('status'));

        return response()->json(['success' => true]);
    }

    public function refund(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $this->service->processRefund($id, $request->input('reason'));

        return redirect()->back()->with('success', '退款處理完成');
    }
}
