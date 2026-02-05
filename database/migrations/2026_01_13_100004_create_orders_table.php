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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique(); // 訂單編號

            // 訂單狀態: pending(待付款), paid(已付款), processing(處理中), shipped(已出貨), completed(已完成), cancelled(已取消)
            $table->enum('status', ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');

            // 金額相關
            $table->decimal('subtotal', 10, 2); // 小計
            $table->decimal('shipping_fee', 10, 2)->default(0); // 運費
            $table->decimal('discount', 10, 2)->default(0); // 折扣金額
            $table->decimal('total', 10, 2); // 總計

            // 收件人資訊
            $table->string('shipping_name'); // 收件人姓名
            $table->string('shipping_phone'); // 收件人電話
            $table->string('shipping_city'); // 城市
            $table->string('shipping_district'); // 區域
            $table->text('shipping_address'); // 詳細地址

            // 付款相關
            $table->string('payment_method')->nullable(); // 付款方式: credit_card, bank_transfer, cash_on_delivery
            $table->timestamp('paid_at')->nullable(); // 付款時間

            // 備註
            $table->text('note')->nullable(); // 訂單備註

            $table->timestamps();

            // 建立索引
            $table->index('order_number');
            $table->index('status');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
