<?php

namespace App\Services\Inventory;

use App\Models\Product\Product;

class InventoryService
{
    public function getAllProducts()
    {
        return Product::orderBy('stock', 'asc')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
                'is_active' => $product->is_active,
                'image' => $product->image ? '/images/' . $product->image : null,
                'is_low_stock' => $product->stock <= 5,
            ];
        });
    }

    public function getLowStockProducts(int $threshold = 5)
    {
        return Product::where('stock', '<=', $threshold)
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->get();
    }

    public function adjustStock(int $productId, int $change, ?string $reason = null): Product
    {
        $product = Product::findOrFail($productId);
        $oldStock = $product->stock;
        $newStock = max(0, $oldStock + $change);

        $product->update(['stock' => $newStock]);

        $this->logInventoryChange(
            $product,
            'adjust',
            $oldStock,
            $change,
            $newStock,
            $reason
        );

        return $product->fresh();
    }

    public function deductForOrder(int $productId, int $quantity, int $orderId): void
    {
        $product = Product::findOrFail($productId);
        $oldStock = $product->stock;
        $newStock = max(0, $oldStock - $quantity);

        $product->update(['stock' => $newStock]);

        $this->logInventoryChange(
            $product,
            'order_deduct',
            $oldStock,
            -$quantity,
            $newStock,
            null,
            $orderId
        );
    }

    public function restoreForOrder(int $productId, int $quantity, int $orderId): void
    {
        $product = Product::findOrFail($productId);
        $oldStock = $product->stock;
        $newStock = $oldStock + $quantity;

        $product->update(['stock' => $newStock]);

        $this->logInventoryChange(
            $product,
            'order_restore',
            $oldStock,
            $quantity,
            $newStock,
            null,
            $orderId
        );
    }

    protected function logInventoryChange(
        Product $product,
        string $action,
        int $oldStock,
        int $change,
        int $newStock,
        ?string $reason = null,
        ?int $orderId = null
    ): void {
        activity('inventory')
            ->performedOn($product)
            ->withProperties([
                'action' => $action,
                'product_name' => $product->name,
                'quantity_before' => $oldStock,
                'quantity_change' => $change,
                'quantity_after' => $newStock,
                'reason' => $reason,
                'order_id' => $orderId,
            ])
            ->log($this->getInventoryActionDescription($action, $product->name, $change));
    }

    protected function getInventoryActionDescription(string $action, string $productName, int $change): string
    {
        $changeStr = $change >= 0 ? "+{$change}" : (string) $change;
        return match ($action) {
            'adjust' => "手動調整庫存: {$productName} ({$changeStr})",
            'order_deduct' => "訂單扣除庫存: {$productName} ({$change})",
            'order_restore' => "訂單退回庫存: {$productName} (+{$change})",
            default => "{$action}: {$productName}",
        };
    }
}
