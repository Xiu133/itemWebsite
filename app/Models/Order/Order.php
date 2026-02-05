<?php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // 物流狀態常數
    const LOGISTICS_STATUS_PENDING = 'pending';           // 待建立物流單
    const LOGISTICS_STATUS_CREATED = 'created';           // 已建立物流單
    const LOGISTICS_STATUS_PICKED_UP = 'picked_up';       // 已取件
    const LOGISTICS_STATUS_IN_TRANSIT = 'in_transit';     // 運送中
    const LOGISTICS_STATUS_DELIVERED = 'delivered';       // 已送達
    const LOGISTICS_STATUS_FAILED = 'failed';             // 配送失敗

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'subtotal',
        'shipping_fee',
        'discount',
        'total',
        'shipping_name',
        'shipping_phone',
        'shipping_city',
        'shipping_district',
        'shipping_address',
        'payment_method',
        'paid_at',
        'note',
        // 物流相關欄位
        'logistics_trade_no',
        'all_pay_logistics_id',
        'logistics_type',
        'logistics_sub_type',
        'logistics_status',
        'shipped_at',
        'logistics_response_data',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'logistics_response_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusTextAttribute()
    {
        $statusMap = [
            'pending' => '待付款',
            'paid' => '已付款',
            'processing' => '處理中',
            'shipped' => '已出貨',
            'completed' => '已完成',
            'cancelled' => '已取消',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    public function getPaymentMethodTextAttribute()
    {
        $methodMap = [
            'credit_card' => '信用卡',
            'bank_transfer' => '銀行轉帳',
            'cash_on_delivery' => '貨到付款',
        ];

        return $methodMap[$this->payment_method] ?? $this->payment_method;
    }

    public function getLogisticsStatusTextAttribute()
    {
        $statusMap = [
            self::LOGISTICS_STATUS_PENDING => '待建立物流單',
            self::LOGISTICS_STATUS_CREATED => '已建立物流單',
            self::LOGISTICS_STATUS_PICKED_UP => '已取件',
            self::LOGISTICS_STATUS_IN_TRANSIT => '運送中',
            self::LOGISTICS_STATUS_DELIVERED => '已送達',
            self::LOGISTICS_STATUS_FAILED => '配送失敗',
        ];

        return $statusMap[$this->logistics_status] ?? $this->logistics_status ?? '未設定';
    }

    public function getLogisticsTypeTextAttribute()
    {
        $typeMap = [
            'HOME' => '宅配',
            'CVS' => '超商取貨',
        ];

        return $typeMap[$this->logistics_type] ?? $this->logistics_type ?? '未設定';
    }

    public function getLogisticsSubTypeTextAttribute()
    {
        $subTypeMap = [
            'TCAT' => '黑貓宅急便',
            'ECAN' => '宅配通',
            'FAMI' => '全家',
            'UNIMART' => '7-ELEVEN',
            'HILIFE' => '萊爾富',
        ];

        return $subTypeMap[$this->logistics_sub_type] ?? $this->logistics_sub_type ?? '未設定';
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $date . $random;
    }
}
