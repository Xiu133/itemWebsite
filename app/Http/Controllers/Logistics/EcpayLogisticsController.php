<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Services\Logistics\EcpayLogisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EcpayLogisticsController extends Controller
{
    protected $logisticsService;

    public function __construct(EcpayLogisticsService $logisticsService)
    {
        $this->logisticsService = $logisticsService;
    }

    /**
     * 建立物流單
     */
    public function create(Order $order)
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
     * 物流狀態通知（綠界回調）
     */
    public function statusNotify(Request $request)
    {
        Log::info('收到綠界物流狀態通知', $request->all());

        try {
            $result = $this->logisticsService->handleStatusNotify($request->all());

            // 綠界要求回傳格式
            if ($result['success']) {
                return response('1|OK');
            }

            return response('0|' . ($result['message'] ?? 'FAIL'));

        } catch (\Exception $e) {
            Log::error('處理物流狀態通知失敗', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response('0|' . $e->getMessage());
        }
    }

    /**
     * 查詢物流狀態
     */
    public function query(Order $order)
    {
        try {
            if (!$order->logistics_trade_no) {
                return response()->json([
                    'success' => false,
                    'message' => '此訂單尚未建立物流單',
                ], 400);
            }

            $result = $this->logisticsService->queryLogisticsInfo($order->logistics_trade_no);

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
                        'logistics_type_text' => $order->logistics_type_text,
                        'logistics_sub_type_text' => $order->logistics_sub_type_text,
                        'shipped_at' => $order->shipped_at?->format('Y-m-d H:i:s'),
                    ],
                    'ecpay_info' => $result['data'] ?? null,
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
}
