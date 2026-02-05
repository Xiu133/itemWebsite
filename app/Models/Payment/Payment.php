<?php

namespace App\Models\Payment;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'trade_no',
        'payment_method',
        'amount',
        'status',
        'ecpay_trade_no',
        'payment_date',
        'response_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'response_data' => 'array',
    ];

    // 付款狀態常數
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusTextAttribute()
    {
        $statusMap = [
            self::STATUS_PENDING => '待付款',
            self::STATUS_PAID => '已付款',
            self::STATUS_FAILED => '付款失敗',
            self::STATUS_REFUNDED => '已退款',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    public function getPaymentMethodTextAttribute()
    {
        $methodMap = [
            'Credit' => '信用卡',
            'WebATM' => '網路 ATM',
            'ATM' => 'ATM 轉帳',
            'CVS' => '超商代碼',
            'BARCODE' => '超商條碼',
            'cash_on_delivery' => '貨到付款',
        ];

        return $methodMap[$this->payment_method] ?? $this->payment_method;
    }
}
