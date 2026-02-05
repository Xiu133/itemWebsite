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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // SKU (Stock Keeping Unit) 庫存單位
            $table->string('sku')->unique(); // 唯一識別碼

            // 規格屬性（依商品類型可能有不同組合）
            $table->string('size')->nullable(); // 尺寸: S, M, L, XL
            $table->string('color')->nullable(); // 顏色: 黑, 白, 紅
            $table->string('material')->nullable(); // 材質
            $table->json('attributes')->nullable(); // 其他自定義屬性（JSON格式，更靈活）

            // 價格與庫存（每個規格可能不同）
            $table->decimal('price', 10, 2)->nullable(); // 規格價格（null則使用主商品價格）
            $table->integer('stock')->default(0); // 規格庫存

            // 圖片（規格可能有專屬圖片）
            $table->string('image')->nullable();

            // 狀態
            $table->boolean('is_active')->default(true); // 是否啟用此規格

            $table->timestamps();

            // 建立索引
            $table->index('product_id');
            $table->index('sku');

            // 確保同一商品的相同規格組合不重複
            $table->unique(['product_id', 'size', 'color']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
