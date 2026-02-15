<?php

namespace App\Services\Payment;

use App\Models\Payment\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentManageService
{
    public function getAllPayments(?string $status = null)
    {
        $query = Payment::with('order.user')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function getPaymentById(int $id): Payment
    {
        return Payment::with('order.user')->findOrFail($id);
    }

    public function updateStatus(int $paymentId, string $newStatus): Payment
    {
        $payment = Payment::findOrFail($paymentId);
        $oldStatus = $payment->status;

        if ($oldStatus === $newStatus) {
            return $payment;
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === Payment::STATUS_PAID && !$payment->payment_date) {
            $updateData['payment_date'] = now();
        }

        $payment->update($updateData);

        // Update order status if payment is marked as paid
        if ($newStatus === Payment::STATUS_PAID) {
            $payment->order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        $this->logStatusChange($payment, $oldStatus, $newStatus);

        return $payment->fresh();
    }

    public function processRefund(int $paymentId, ?string $reason = null): Payment
    {
        $payment = Payment::findOrFail($paymentId);
        $oldStatus = $payment->status;

        $payment->update(['status' => Payment::STATUS_REFUNDED]);

        activity('payment')
            ->performedOn($payment)
            ->withProperties([
                'action' => 'refund',
                'trade_no' => $payment->trade_no,
                'old_status' => $oldStatus,
                'new_status' => Payment::STATUS_REFUNDED,
                'amount' => $payment->amount,
                'reason' => $reason,
            ])
            ->log("支付退款: {$payment->trade_no}");

        return $payment->fresh();
    }

    public function getStatistics(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 8 次查詢合併為 1 次，使用 PostgreSQL COUNT/SUM FILTER 語法
        $stats = DB::table('payments')
            ->selectRaw("
                COUNT(*) as total_count,
                COUNT(*) FILTER (WHERE status = ?) as pending_count,
                COUNT(*) FILTER (WHERE status = ?) as paid_count,
                COUNT(*) FILTER (WHERE status = ?) as refunded_count,
                COALESCE(SUM(amount) FILTER (WHERE status = ?), 0) as total_revenue,
                COALESCE(SUM(amount) FILTER (WHERE status = ? AND payment_date >= ?), 0) as monthly_revenue,
                COALESCE(SUM(amount) FILTER (WHERE status = ? AND payment_date >= ? AND payment_date <= ?), 0) as last_month_revenue
            ", [
                Payment::STATUS_PENDING,
                Payment::STATUS_PAID,
                Payment::STATUS_REFUNDED,
                Payment::STATUS_PAID,
                Payment::STATUS_PAID, $startOfMonth,
                Payment::STATUS_PAID, $startOfLastMonth, $endOfLastMonth,
            ])
            ->first();

        return [
            'total_revenue' => (float) $stats->total_revenue,
            'monthly_revenue' => (float) $stats->monthly_revenue,
            'last_month_revenue' => (float) $stats->last_month_revenue,
            'pending_count' => (int) $stats->pending_count,
            'paid_count' => (int) $stats->paid_count,
            'refunded_count' => (int) $stats->refunded_count,
            'total_count' => (int) $stats->total_count,
        ];
    }

    public function getRevenueByDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $payments = Payment::where('status', Payment::STATUS_PAID)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $payments->pluck('total', 'date')->toArray();
    }

    protected function logStatusChange(Payment $payment, string $oldStatus, string $newStatus): void
    {
        activity('payment')
            ->performedOn($payment)
            ->withProperties([
                'action' => 'status_change',
                'trade_no' => $payment->trade_no,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'amount' => $payment->amount,
            ])
            ->log("支付狀態變更: {$payment->trade_no} ({$oldStatus} → {$newStatus})");
    }
}
