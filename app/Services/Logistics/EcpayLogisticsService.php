<?php

namespace App\Services\Logistics;

use App\Models\Order\Order;
use App\Repositories\Contracts\Logistics\LogisticsRepositoryInterface;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Response\VerifiedArrayResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\Activity;

class EcpayLogisticsService
{
    protected $logisticsRepository;
    protected $factory;

    protected $hashKey;
    protected $hashIv;
    protected $merchantId;
    protected $isProduction;
    protected $testMode;

    // 寄件人資訊
    protected $senderName;
    protected $senderPhone;
    protected $senderCellPhone;
    protected $senderZipCode;
    protected $senderAddress;

    public function __construct(LogisticsRepositoryInterface $logisticsRepository)
    {
        $this->logisticsRepository = $logisticsRepository;

        // 從 config 讀取物流設定
        $this->hashKey = config('ecpay.logistics.hash_key', '5294y06JbISpM5x9');
        $this->hashIv = config('ecpay.logistics.hash_iv', 'v77hoKGq4kWxNNIS');
        $this->merchantId = config('ecpay.logistics.merchant_id', '2000132');
        $this->isProduction = config('ecpay.logistics.production', false);
        $this->testMode = config('ecpay.logistics.test_mode', true);

        // 寄件人資訊
        $this->senderName = config('ecpay.logistics.sender_name', '測試商店');
        $this->senderPhone = config('ecpay.logistics.sender_phone', '0912345678');
        $this->senderCellPhone = config('ecpay.logistics.sender_cell_phone', '0912345678');
        $this->senderZipCode = config('ecpay.logistics.sender_zipcode', '11560');
        $this->senderAddress = config('ecpay.logistics.sender_address', '台北市南港區三重路19-2號6樓');

        // 物流使用 MD5 雜湊
        $this->factory = new Factory([
            'hashKey' => $this->hashKey,
            'hashIv' => $this->hashIv,
            'hashMethod' => 'md5',
        ]);
    }

    /**
     * 取得物流 API URL
     */
    protected function getLogisticsUrl(): string
    {
        return $this->isProduction
            ? 'https://logistics.ecpay.com.tw/Express/Create'
            : 'https://logistics-stage.ecpay.com.tw/Express/Create';
    }

    /**
     * 取得物流查詢 API URL
     */
    protected function getQueryUrl(): string
    {
        return $this->isProduction
            ? 'https://logistics.ecpay.com.tw/Helper/QueryLogisticsTradeInfo/V4'
            : 'https://logistics-stage.ecpay.com.tw/Helper/QueryLogisticsTradeInfo/V4';
    }

    /**
     * 產生物流交易編號
     */
    public function generateLogisticsTradeNo(): string
    {
        // 綠界規定：MerchantTradeNo 長度最多 20 字元
        return 'LG' . date('YmdHis') . substr(uniqid(), -4);
    }

    /**
     * 建立宅配物流單
     */
    public function createHomeDelivery(Order $order): array
    {
        Log::info('開始建立宅配物流單', ['order_id' => $order->id]);

        return DB::transaction(function () use ($order) {
            // 重新取得訂單並加鎖，防止並發建立重複物流單
            $order = Order::lockForUpdate()->find($order->id);

            if (!$order) {
                throw new \Exception('找不到訂單');
            }

            // 檢查訂單狀態（已付款、處理中，或者是貨到付款的待處理訂單）
            $canCreateShipment = in_array($order->status, ['paid', 'processing']) ||
                ($order->payment_method === 'cash_on_delivery' && $order->status === 'pending');

            if (!$canCreateShipment) {
                throw new \Exception('訂單狀態不允許建立物流單，請先完成付款');
            }

            // 檢查是否已建立物流單（在鎖定狀態下檢查，避免 race condition）
            if ($order->all_pay_logistics_id) {
                throw new \Exception('此訂單已建立物流單');
            }

            // 產生物流交易編號
            $logisticsTradeNo = $this->generateLogisticsTradeNo();

            // 準備商品名稱
            $goodsName = $this->prepareGoodsName($order);

            // 組合收件地址
            $receiverAddress = $order->shipping_city . $order->shipping_district . $order->shipping_address;

            // 準備請求參數
            $input = [
                'MerchantID' => $this->merchantId,
                'MerchantTradeNo' => $logisticsTradeNo,
                'MerchantTradeDate' => date('Y/m/d H:i:s'),
                'LogisticsType' => 'HOME',
                'LogisticsSubType' => 'TCAT', // 黑貓宅急便
                'GoodsAmount' => (int) $order->total,
                'GoodsName' => $goodsName,
                'SenderName' => $this->senderName,
                'SenderCellPhone' => $this->senderCellPhone,
                'SenderZipCode' => $this->senderZipCode,
                'SenderAddress' => $this->senderAddress,
                'ReceiverName' => $order->shipping_name,
                'ReceiverCellPhone' => $order->shipping_phone,
                'ReceiverZipCode' => $this->getZipCode($order->shipping_city, $order->shipping_district),
                'ReceiverAddress' => $receiverAddress,
                'Temperature' => '0001', // 常溫
                'Distance' => '00',      // 同縣市
                'Specification' => '0001', // 60cm
                'ScheduledPickupTime' => '4', // 不限時
                'ScheduledDeliveryTime' => '4', // 不限時
                'ServerReplyURL' => route('ecpay-logistics.status-notify'),
            ];

            Log::info('物流 API 請求參數', $input);

            try {
                // 測試模式：跳過實際 API 呼叫，模擬成功回應
                if ($this->testMode) {
                    Log::info('測試模式：跳過綠界 API 呼叫');
                    $logisticsId = 'TEST' . date('YmdHis') . rand(1000, 9999);
                    $result = [
                        'RtnCode' => '1',
                        'RtnMsg' => '測試模式',
                        'AllPayLogisticsID' => $logisticsId,
                    ];
                } else {
                    $postService = $this->factory->create('PostWithCmvStrResponseService');
                    $response = $postService->post($input, $this->getLogisticsUrl());

                    Log::info('物流 API 回應', ['response' => $response, 'type' => gettype($response)]);

                    // 解析回應
                    $result = $this->parseResponse($response);

                    Log::info('解析後的回應', ['result' => $result]);

                    // 取得回傳碼，支援不同的 key 名稱
                    $rtnCode = $result['RtnCode'] ?? $result['ResCode'] ?? $result['1'] ?? null;
                    $rtnMsg = $result['RtnMsg'] ?? $result['ResMessage'] ?? $result['ErrorMessage'] ?? '未知錯誤';
                    $logisticsId = $result['AllPayLogisticsID'] ?? $result['LogisticsID'] ?? $result['1|AllPayLogisticsID'] ?? null;

                    if ($rtnCode === null || ($rtnCode != '1' && $rtnCode != '300')) {
                        Log::error('建立物流單失敗', $result);
                        throw new \Exception($rtnMsg);
                    }
                }

                // 更新訂單物流資訊
                // 貨到付款訂單：狀態改為 shipped（已出貨），等送達後才完成入帳
                // 已付款訂單：狀態改為 completed
                $newStatus = ($order->payment_method === 'cash_on_delivery')
                    ? 'shipped'
                    : 'completed';

                $this->logisticsRepository->updateOrderLogistics($order->id, [
                    'logistics_trade_no' => $logisticsTradeNo,
                    'all_pay_logistics_id' => $logisticsId,
                    'logistics_type' => 'HOME',
                    'logistics_sub_type' => 'TCAT',
                    'logistics_status' => Order::LOGISTICS_STATUS_CREATED,
                    'logistics_response_data' => $result,
                    'status' => $newStatus,
                    'shipped_at' => now(),
                ]);

                Log::info('物流單建立成功', [
                    'order_id' => $order->id,
                    'logistics_trade_no' => $logisticsTradeNo,
                    'all_pay_logistics_id' => $logisticsId,
                ]);

                // 記錄 activity log
                $this->logAction($order, 'create', "建立物流單: {$order->order_number}", [
                    'logistics_trade_no' => $logisticsTradeNo,
                    'all_pay_logistics_id' => $logisticsId,
                    'logistics_type' => 'HOME',
                    'logistics_sub_type' => 'TCAT',
                ]);

                return [
                    'success' => true,
                    'message' => '物流單建立成功',
                    'data' => [
                        'logistics_trade_no' => $logisticsTradeNo,
                        'all_pay_logistics_id' => $logisticsId,
                    ],
                ];

            } catch (\Exception $e) {
                Log::error('建立物流單例外', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * 處理物流狀態通知
     */
    public function handleStatusNotify(array $data): array
    {
        Log::info('收到物流狀態通知', $data);

        // 驗證 CheckMacValue
        if (!$this->verifyCheckMacValue($data)) {
            Log::error('物流狀態通知 CheckMacValue 驗證失敗', $data);
            return [
                'success' => false,
                'message' => 'CheckMacValue 驗證失敗',
            ];
        }

        $logisticsTradeNo = $data['MerchantTradeNo'] ?? '';
        $logisticsStatus = $data['RtnCode'] ?? '';

        // 查詢訂單
        $order = $this->logisticsRepository->findOrderByLogisticsTradeNo($logisticsTradeNo);

        if (!$order) {
            Log::error('找不到對應的訂單', ['logistics_trade_no' => $logisticsTradeNo]);
            return [
                'success' => false,
                'message' => '找不到對應的訂單',
            ];
        }

        // 轉換物流狀態
        $status = $this->mapLogisticsStatus($logisticsStatus);
        $oldStatus = $order->logistics_status;

        // 已經是相同狀態，略過重複通知
        if ($oldStatus === $status) {
            Log::info('重複的物流狀態通知，略過處理', [
                'order_id' => $order->id,
                'logistics_trade_no' => $logisticsTradeNo,
                'status' => $status,
            ]);
            return [
                'success' => true,
                'message' => '狀態已處理',
            ];
        }

        // 已送達或已失敗的訂單不允許被舊通知覆蓋
        $finalStatuses = [Order::LOGISTICS_STATUS_DELIVERED, Order::LOGISTICS_STATUS_FAILED];
        if (in_array($oldStatus, $finalStatuses)) {
            Log::info('物流已為終態，略過通知', [
                'order_id' => $order->id,
                'current_status' => $oldStatus,
                'incoming_status' => $status,
            ]);
            return [
                'success' => true,
                'message' => '狀態已處理',
            ];
        }

        // 更新訂單物流狀態
        $this->logisticsRepository->updateLogisticsStatus($order->id, $status);

        // 更新物流回應資料
        $this->logisticsRepository->updateOrderLogistics($order->id, [
            'logistics_response_data' => array_merge(
                $order->logistics_response_data ?? [],
                ['latest_notify' => $data]
            ),
        ]);

        Log::info('物流狀態更新成功', [
            'order_id' => $order->id,
            'logistics_status' => $status,
        ]);

        // 記錄 activity log
        $this->logAction($order, 'status_change', "物流狀態變更: {$order->order_number} ({$oldStatus} → {$status})", [
            'logistics_trade_no' => $logisticsTradeNo,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'rtn_code' => $logisticsStatus,
        ]);

        return [
            'success' => true,
            'message' => '狀態更新成功',
        ];
    }

    /**
     * 查詢物流資訊
     */
    public function queryLogisticsInfo(string $logisticsTradeNo): array
    {
        $order = $this->logisticsRepository->findOrderByLogisticsTradeNo($logisticsTradeNo);

        if (!$order || !$order->all_pay_logistics_id) {
            throw new \Exception('找不到物流單資訊');
        }

        $input = [
            'MerchantID' => $this->merchantId,
            'AllPayLogisticsID' => $order->all_pay_logistics_id,
            'TimeStamp' => time(),
        ];

        try {
            $postService = $this->factory->create('PostWithCmvStrResponseService');
            $response = $postService->post($input, $this->getQueryUrl());

            $result = $this->parseResponse($response);

            return [
                'success' => true,
                'data' => $result,
            ];

        } catch (\Exception $e) {
            Log::error('查詢物流資訊失敗', [
                'logistics_trade_no' => $logisticsTradeNo,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * 驗證 CheckMacValue
     */
    protected function verifyCheckMacValue(array $data): bool
    {
        try {
            $checkMacValueService = $this->factory->create('CheckMacValueService');
            $checkMacValue = $data['CheckMacValue'] ?? '';
            unset($data['CheckMacValue']);

            return $checkMacValueService->verify($data, $checkMacValue);
        } catch (\Exception $e) {
            Log::error('驗證 CheckMacValue 發生錯誤', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 準備商品名稱
     */
    protected function prepareGoodsName(Order $order): string
    {
        $items = $order->items ?? collect();

        if ($items->isEmpty()) {
            return '訂單商品';
        }

        // 組合商品名稱，綠界規定最多 60 字元
        $names = $items->map(fn($item) => $item->product_name)->take(3)->toArray();
        $goodsName = implode('、', $names);

        if ($items->count() > 3) {
            $goodsName .= '等';
        }

        if (mb_strlen($goodsName) > 57) {
            $goodsName = mb_substr($goodsName, 0, 57) . '...';
        }

        return $goodsName;
    }

    /**
     * 解析 API 回應
     */
    protected function parseResponse(string|array $response): array
    {
        // 處理 SDK 回傳的陣列格式 {"body": "code|message"}
        if (is_array($response) && isset($response['body'])) {
            return $this->parseBodyString($response['body']);
        }

        if (is_array($response)) {
            return $response;
        }

        // 檢查是否為 pipe 分隔格式
        if (str_contains($response, '|')) {
            return $this->parseBodyString($response);
        }

        $result = [];
        parse_str($response, $result);
        return $result;
    }

    /**
     * 解析 pipe 分隔的回應字串
     * 格式: "RtnCode|RtnMsg" 或 "1|AllPayLogisticsID|..."
     */
    protected function parseBodyString(string $body): array
    {
        $parts = explode('|', $body);

        // 錯誤格式: "errorCode|errorMessage"
        if (count($parts) === 2 && is_numeric($parts[0])) {
            return [
                'RtnCode' => $parts[0],
                'RtnMsg' => $parts[1],
            ];
        }

        // 成功格式: "1|AllPayLogisticsID|BookingNote|..." (綠界物流回傳格式)
        if (count($parts) >= 2 && $parts[0] === '1') {
            return [
                'RtnCode' => '1',
                'AllPayLogisticsID' => $parts[1] ?? null,
                'BookingNote' => $parts[2] ?? null,
            ];
        }

        // 其他格式，回傳原始 body
        return [
            'RtnCode' => $parts[0] ?? null,
            'RtnMsg' => $parts[1] ?? $body,
        ];
    }

    /**
     * 對應物流狀態
     */
    protected function mapLogisticsStatus(string $rtnCode): string
    {
        // 根據綠界物流狀態碼對應
        $statusMap = [
            '300' => Order::LOGISTICS_STATUS_CREATED,     // 訂單處理中
            '310' => Order::LOGISTICS_STATUS_CREATED,     // 訂單建立完成
            '3001' => Order::LOGISTICS_STATUS_PICKED_UP,  // 貨物已取件
            '3003' => Order::LOGISTICS_STATUS_IN_TRANSIT, // 貨物運送中
            '3006' => Order::LOGISTICS_STATUS_DELIVERED,  // 已送達
            '3007' => Order::LOGISTICS_STATUS_FAILED,     // 配送失敗
        ];

        return $statusMap[$rtnCode] ?? Order::LOGISTICS_STATUS_CREATED;
    }

    /**
     * 取得郵遞區號（簡易版，實際使用應有完整對照表）
     */
    protected function getZipCode(string $city, string $district): string
    {
        // 這裡可以接入郵遞區號 API 或資料庫
        // 目前先回傳預設值，實際使用時應替換為正確的郵遞區號
        return '100';
    }

    /**
     * 記錄物流操作日誌
     */
    protected function logAction(Order $order, string $action, string $description, array $properties = []): void
    {
        $activity = activity('logistics')
            ->performedOn($order)
            ->withProperties(array_merge([
                'action' => $action,
                'order_number' => $order->order_number,
            ], $properties));

        if (Auth::check()) {
            $activity->causedBy(Auth::user());
        }

        $activity->log($description);
    }
}
