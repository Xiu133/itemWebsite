<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MerchantDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'pending_orders' => Order::whereIn('status', ['paid', 'processing'])->count(),
            'monthly_revenue' => Payment::where('status', 'paid')
                ->where('payment_date', '>=', Carbon::now()->startOfMonth())
                ->sum('amount'),
            'low_stock_products' => Product::where('stock', '<=', 5)->where('is_active', true)->count(),
        ];

        return view('merchant.dashboard', compact('stats'));
    }
}
