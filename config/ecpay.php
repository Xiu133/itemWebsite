<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 綠界金流設定
    |--------------------------------------------------------------------------
    |
    | 這裡設定綠界金流的相關參數
    | 測試環境使用測試商店代號：2000132
    | 正式環境請更換為您的商店代號
    |
    */

    // 是否為正式環境
    'production' => env('ECPAY_PRODUCTION', false),

    // 商店代號
    'merchant_id' => env('ECPAY_MERCHANT_ID', '2000132'),

    // HashKey
    'hash_key' => env('ECPAY_HASH_KEY', '5294y06JbISpM5x9'),

    // HashIV
    'hash_iv' => env('ECPAY_HASH_IV', 'v77hoKGq4kWxNNIS'),

    /*
    |--------------------------------------------------------------------------
    | 付款方式設定
    |--------------------------------------------------------------------------
    */

    // 預設付款方式 (ALL, Credit, WebATM, ATM, CVS, BARCODE)
    'default_payment_method' => env('ECPAY_DEFAULT_PAYMENT', 'ALL'),

    // 信用卡分期期數 (0 表示不分期，可選 3, 6, 12, 18, 24)
    'credit_installment' => env('ECPAY_CREDIT_INSTALLMENT', 0),

    /*
    |--------------------------------------------------------------------------
    | 其他設定
    |--------------------------------------------------------------------------
    */

    // 訂單有效時間（分鐘）
    'order_expire_minutes' => env('ECPAY_ORDER_EXPIRE', 20),

    /*
    |--------------------------------------------------------------------------
    | 物流設定
    |--------------------------------------------------------------------------
    |
    | 綠界物流相關參數
    | 測試環境使用測試商店代號：2000132
    |
    */

    'logistics' => [
        // 是否為正式環境
        'production' => env('ECPAY_LOGISTICS_PRODUCTION', false),

        // 物流商店代號（可與金流共用或分開設定）
        'merchant_id' => env('ECPAY_LOGISTICS_MERCHANT_ID', '2000132'),

        // 物流 HashKey（物流使用 MD5）
        'hash_key' => env('ECPAY_LOGISTICS_HASH_KEY', '5294y06JbISpM5x9'),

        // 物流 HashIV
        'hash_iv' => env('ECPAY_LOGISTICS_HASH_IV', 'v77hoKGq4kWxNNIS'),

        // 寄件人資訊
        'sender_name' => env('ECPAY_SENDER_NAME', '測試商店'),
        'sender_phone' => env('ECPAY_SENDER_PHONE', '0912345678'),
        'sender_cell_phone' => env('ECPAY_SENDER_CELL_PHONE', '0912345678'),
        'sender_zipcode' => env('ECPAY_SENDER_ZIPCODE', '11560'),
        'sender_address' => env('ECPAY_SENDER_ADDRESS', '台北市南港區三重路19-2號6樓'),
    ],
];
