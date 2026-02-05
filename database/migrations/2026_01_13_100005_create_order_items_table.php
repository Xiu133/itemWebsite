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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict'); // 使用restrict避免商品被刪除時訂單資料遺失

            // 商品資訊快照（保留當時的商品資訊）
            $table->string('product_name'); // 商品名稱快照
            $table->string('product_image')->nullable(); // 商品圖片快照

            // 數量與價格
            $table->integer('quantity'); // 購買數量
            $table->decimal('price', 10, 2); // 單價快照（購買時的價格）
            $table->decimal('subtotal', 10, 2); // 小計 (quantity * price)

            $table->timestamps();

            // 建立索引
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
