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
        Schema::table('cart_items', function (Blueprint $table) {
            // 刪除舊的唯一約束
            $table->dropUnique(['cart_id', 'product_id']);

            // 添加 product_variant_id（可選，因為有些商品可能沒有規格）
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->onDelete('cascade');

            // 新的唯一約束：同一購物車內，相同商品+規格組合只能有一筆
            $table->unique(['cart_id', 'product_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // 刪除新的唯一約束
            $table->dropUnique(['cart_id', 'product_id', 'product_variant_id']);

            // 移除 product_variant_id
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');

            // 恢復舊的唯一約束
            $table->unique(['cart_id', 'product_id']);
        });
    }
};
