<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MerchantDashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        // 將 products 的 2 次查詢合併為 1 次
        $productStats = DB::table('products')
            ->selectRaw("
                COUNT(*) as total_products,
                COUNT(*) FILTER (WHERE stock <= 5 AND is_active = true AND deleted_at IS NULL) as low_stock_products
            ")
            ->whereNull('deleted_at')
            ->first();

        // 將 orders + payments 的 2 次查詢合併為 1 次
        // orders 和 payments 是不同表，分開各做 1 次查詢即可
        $pendingOrders = DB::table('orders')
            ->whereIn('status', ['paid', 'processing'])
            ->count();

        $monthlyRevenue = DB::table('payments')
            ->where('status', Payment::STATUS_PAID)
            ->where('payment_date', '>=', $startOfMonth)
            ->sum('amount');

        $stats = [
            'total_products' => (int) $productStats->total_products,
            'pending_orders' => $pendingOrders,
            'monthly_revenue' => (float) $monthlyRevenue,
            'low_stock_products' => (int) $productStats->low_stock_products,
        ];

        return view('merchant.dashboard', compact('stats'));
    }
}
