<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $service;

    public function __construct(InventoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $products = $this->service->getAllProducts();
        $lowStockCount = $products->filter(fn($p) => $p['is_low_stock'])->count();

        return view('inventory.index', compact('products', 'lowStockCount'));
    }

    public function adjust(Request $request, int $id)
    {
        $request->validate([
            'change' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $this->service->adjustStock(
            $id,
            $request->integer('change'),
            $request->input('reason')
        );

        return response()->json(['success' => true]);
    }
}
