<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // products 表：is_active, stock, user_id 缺少索引
        Schema::table('products', function (Blueprint $table) {
            $table->index('user_id', 'idx_products_user_id');
        });

        // product_tag 表：tag_id 缺少反向查詢索引
        Schema::table('product_tag', function (Blueprint $table) {
            $table->index('tag_id', 'idx_product_tag_tag_id');
        });

        // carts 表：user_id 缺少索引
        Schema::table('carts', function (Blueprint $table) {
            $table->index('user_id', 'idx_carts_user_id');
        });

        // brands 表：is_active 缺少索引
        Schema::table('brands', function (Blueprint $table) {
            $table->index('is_active', 'idx_brands_is_active');
        });

        // PostgreSQL Partial Index：只索引上架中且未刪除的商品
        DB::statement('
            CREATE INDEX idx_products_active
            ON products (id, category_id, brand_id, stock)
            WHERE is_active = true AND deleted_at IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_products_active');

        Schema::table('brands', function (Blueprint $table) {
            $table->dropIndex('idx_brands_is_active');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('idx_carts_user_id');
        });

        Schema::table('product_tag', function (Blueprint $table) {
            $table->dropIndex('idx_product_tag_tag_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_user_id');
        });
    }
};
