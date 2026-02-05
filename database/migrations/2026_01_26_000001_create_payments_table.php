<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('trade_no')->unique()->comment('商店訂單編號');
            $table->string('payment_method')->nullable()->comment('付款方式');
            $table->decimal('amount', 10, 2)->comment('付款金額');
            $table->string('status')->default('pending')->comment('付款狀態');
            $table->string('ecpay_trade_no')->nullable()->comment('綠界交易編號');
            $table->timestamp('payment_date')->nullable()->comment('付款時間');
            $table->json('response_data')->nullable()->comment('綠界回傳資料');
            $table->timestamps();

            $table->index('trade_no');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
