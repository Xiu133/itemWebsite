<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use App\Services\Logistics\EcpayLogisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogisticsManageController extends Controller
{
    protected $logisticsService;

    public function __construct(EcpayLogisticsService $logisticsService)
    {
        $this->logisticsService = $logisticsService;
    }

    /**
     * 物流管理列表
     */
    public function index(Request $request)
    {
        $query = Order::with('items')
            ->where(function ($q) {
                // 已付款的訂單
                $q->whereIn('status', ['paid', 'processing', 'shipped', 'completed'])
                  // 或者貨到付款的待處理訂單（尚未付款但需要出貨）
                  ->orWhere(function ($subQ) {
                      $subQ->where('payment_method', 'cash_on_delivery')
                           ->where('status', 'pending');
                  });
            });

        // 篩選物流狀態
        if ($request->filled('logistics_status')) {
            if ($request->logistics_status === 'pending') {
                $query->whereNull('logistics_trade_no');
            } else {
                $query->where('logistics_status', $request->logistics_status);
            }
        }

        // 搜尋訂單編號
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // 4 次查詢合併為 1 次，使用 PostgreSQL COUNT FILTER 語法
        $rawStats = DB::table('orders')
            ->selectRaw("
                COUNT(*) FILTER (WHERE
                    (status IN ('paid', 'processing') OR (payment_method = 'cash_on_delivery' AND status = 'pending'))
                    AND logistics_trade_no IS NULL
                ) as pending_shipment,
                COUNT(*) FILTER (WHERE logistics_status = ?) as in_transit,
                COUNT(*) FILTER (WHERE logistics_status = ?) as delivered,
                COUNT(*) FILTER (WHERE logistics_trade_no IS NOT NULL) as total_shipped
            ", [
                Order::LOGISTICS_STATUS_IN_TRANSIT,
                Order::LOGISTICS_STATUS_DELIVERED,
            ])
            ->first();

        $stats = [
            'pending_shipment' => (int) $rawStats->pending_shipment,
            'in_transit' => (int) $rawStats->in_transit,
            'delivered' => (int) $rawStats->delivered,
            'total_shipped' => (int) $rawStats->total_shipped,
        ];

        return view('logistics.manage.index', compact('orders', 'stats'));
    }

    /**
     * 建立物流單
     */
    public function createShipment(Order $order)
    {
        try {
            $result = $this->logisticsService->createHomeDelivery($order);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('建立物流單失敗', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 批次建立物流單
     */
    public function batchCreateShipment(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        $orderIds = $request->order_ids;
        $results = [
            'success' => [],
            'failed' => [],
        ];

        // 取得符合條件的訂單
        $orders = Order::whereIn('id', $orderIds)
            ->whereNull('logistics_trade_no')
            ->where(function ($q) {
                $q->whereIn('status', ['paid', 'processing'])
                  ->orWhere(function ($subQ) {
                      $subQ->where('payment_method', 'cash_on_delivery')
                           ->where('status', 'pending');
                  });
            })
            ->get();

        foreach ($orders as $order) {
            try {
                $result = $this->logisticsService->createHomeDelivery($order);

                if ($result['success']) {
                    $results['success'][] = [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'logistics_id' => $result['data']['all_pay_logistics_id'] ?? null,
                    ];
                } else {
                    $results['failed'][] = [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'message' => $result['message'] ?? '建立失敗',
                    ];
                }
            } catch (\Exception $e) {
                Log::error('批次建立物流單失敗', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);

                $results['failed'][] = [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $totalSuccess = count($results['success']);
        $totalFailed = count($results['failed']);

        Log::info('批次建立物流單完成', [
            'total_requested' => count($orderIds),
            'success_count' => $totalSuccess,
            'failed_count' => $totalFailed,
        ]);

        return response()->json([
            'success' => true,
            'message' => "批次處理完成：成功 {$totalSuccess} 筆，失敗 {$totalFailed} 筆",
            'data' => $results,
        ]);
    }

    /**
     * 查詢物流狀態
     */
    public function queryStatus(Order $order)
    {
        try {
            if (!$order->logistics_trade_no) {
                return response()->json([
                    'success' => false,
                    'message' => '此訂單尚未建立物流單',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'logistics_trade_no' => $order->logistics_trade_no,
                        'all_pay_logistics_id' => $order->all_pay_logistics_id,
                        'logistics_status' => $order->logistics_status,
                        'logistics_status_text' => $order->logistics_status_text,
                        'shipped_at' => $order->shipped_at?->format('Y-m-d H:i:s'),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('查詢物流狀態失敗', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 手動更新物流狀態
     */
    public function updateStatus(Request $request, Order $order)
    {
        try {
            $request->validate([
                'status' => 'required|in:created,picked_up,in_transit,delivered,failed',
            ]);

            if (!$order->logistics_trade_no) {
                return response()->json([
                    'success' => false,
                    'message' => '此訂單尚未建立物流單',
                ], 400);
            }

            DB::beginTransaction();

            $updateData = [
                'logistics_status' => $request->status,
            ];

            // 如果是貨到付款訂單且狀態更新為「已送達」，自動完成付款入帳
            $paymentCompleted = false;
            if ($request->status === 'delivered' && $order->payment_method === 'cash_on_delivery') {
                $now = now();

                // 更新訂單狀態為已完成
                $updateData['status'] = 'completed';
                $updateData['paid_at'] = $now;

                // 建立或更新付款記錄
                Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'trade_no' => 'COD-' . $order->order_number,
                        'payment_method' => 'cash_on_delivery',
                        'amount' => $order->total,
                        'status' => Payment::STATUS_PAID,
                        'payment_date' => $now,
                        'response_data' => [
                            'type' => 'cash_on_delivery',
                            'confirmed_at' => $now->toDateTimeString(),
                            'confirmed_by' => 'logistics_delivery',
                        ],
                    ]
                );

                $paymentCompleted = true;

                Log::info('貨到付款訂單已送達，完成入帳', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => $order->total,
                ]);
            }

            $order->update($updateData);

            DB::commit();

            Log::info('手動更新物流狀態', [
                'order_id' => $order->id,
                'new_status' => $request->status,
                'payment_completed' => $paymentCompleted,
            ]);

            $message = '狀態更新成功';
            if ($paymentCompleted) {
                $message = '狀態更新成功，貨到付款金額已入帳';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'logistics_status' => $order->logistics_status,
                    'logistics_status_text' => $order->logistics_status_text,
                    'payment_completed' => $paymentCompleted,
                    'order_status' => $order->status,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('更新物流狀態失敗', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
