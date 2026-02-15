<?php

namespace App\Services\Order;

use App\Models\Order\Order;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;

class OrderManageService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function getAllOrders(?string $status = null)
    {
        $query = Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function getOrderById(int $id): Order
    {
        return Order::with(['user', 'items.product'])->findOrFail($id);
    }

    public function updateStatus(int $orderId, string $newStatus): Order
    {
        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;

        if ($oldStatus === $newStatus) {
            return $order;
        }

        $order->update(['status' => $newStatus]);

        // Handle inventory changes based on status
        if ($newStatus === 'cancelled' && in_array($oldStatus, ['pending', 'paid', 'processing'])) {
            // Restore inventory when order is cancelled
            foreach ($order->items as $item) {
                $this->inventoryService->restoreForOrder(
                    $item->product_id,
                    $item->quantity,
                    $order->id
                );
            }
        }

        $this->logStatusChange($order, $oldStatus, $newStatus);

        return $order->fresh();
    }

    public function addNote(int $orderId, string $note): Order
    {
        $order = Order::findOrFail($orderId);
        $oldNote = $order->note;

        $order->update(['note' => $note]);

        activity('order')
            ->performedOn($order)
            ->withProperties([
                'action' => 'note_add',
                'order_number' => $order->order_number,
                'old_note' => $oldNote,
                'new_note' => $note,
            ])
            ->log("訂單備註更新: {$order->order_number}");

        return $order->fresh();
    }

    public function getStatistics(): array
    {
        // 7 次查詢合併為 1 次，使用 PostgreSQL COUNT FILTER 語法
        $stats = DB::table('orders')
            ->selectRaw("
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE status = 'pending') as pending,
                COUNT(*) FILTER (WHERE status = 'paid') as paid,
                COUNT(*) FILTER (WHERE status = 'processing') as processing,
                COUNT(*) FILTER (WHERE status = 'shipped') as shipped,
                COUNT(*) FILTER (WHERE status = 'completed') as completed,
                COUNT(*) FILTER (WHERE status = 'cancelled') as cancelled
            ")
            ->first();

        return [
            'pending' => (int) $stats->pending,
            'paid' => (int) $stats->paid,
            'processing' => (int) $stats->processing,
            'shipped' => (int) $stats->shipped,
            'completed' => (int) $stats->completed,
            'cancelled' => (int) $stats->cancelled,
            'total' => (int) $stats->total,
        ];
    }

    protected function logStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        activity('order')
            ->performedOn($order)
            ->withProperties([
                'action' => 'status_change',
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ])
            ->log("訂單狀態變更: {$order->order_number} ({$oldStatus} → {$newStatus})");
    }
}
