<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 物流交易編號（商店自訂）
            $table->string('logistics_trade_no')->nullable()->after('paid_at');

            // 綠界物流編號（綠界回傳的 AllPayLogisticsID）
            $table->string('all_pay_logistics_id')->nullable()->after('logistics_trade_no');

            // 物流類型: HOME(宅配), CVS(超商取貨)
            $table->string('logistics_type')->nullable()->after('all_pay_logistics_id');

            // 物流子類型: TCAT(黑貓), ECAN(宅配通), FAMI(全家), UNIMART(7-11), HILIFE(萊爾富)
            $table->string('logistics_sub_type')->nullable()->after('logistics_type');

            // 物流狀態
            $table->string('logistics_status')->nullable()->after('logistics_sub_type');

            // 出貨時間
            $table->timestamp('shipped_at')->nullable()->after('logistics_status');

            // 物流回傳資料（JSON）
            $table->json('logistics_response_data')->nullable()->after('shipped_at');

            // 建立索引
            $table->index('logistics_trade_no');
            $table->index('all_pay_logistics_id');
            $table->index('logistics_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['logistics_trade_no']);
            $table->dropIndex(['all_pay_logistics_id']);
            $table->dropIndex(['logistics_status']);

            $table->dropColumn([
                'logistics_trade_no',
                'all_pay_logistics_id',
                'logistics_type',
                'logistics_sub_type',
                'logistics_status',
                'shipped_at',
                'logistics_response_data',
            ]);
        });
    }
};
