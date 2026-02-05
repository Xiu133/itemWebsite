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
        // 先移除 cart_items 中的 product_variant_id 欄位
        Schema::table('cart_items', function (Blueprint $table) {
            // 刪除包含 product_variant_id 的唯一約束
            $table->dropUnique(['cart_id', 'product_id', 'product_variant_id']);

            // 移除外鍵和欄位
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');

            // 恢復原本的唯一約束
            $table->unique(['cart_id', 'product_id']);
        });

        // 刪除 product_variants 表
        Schema::dropIfExists('product_variants');

        // 刪除 personal_access_tokens 表
        Schema::dropIfExists('personal_access_tokens');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 重建 personal_access_tokens 表
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        // 重建 product_variants 表
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->json('attributes')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('product_id');
            $table->index('sku');
            $table->unique(['product_id', 'size', 'color']);
        });

        // 恢復 cart_items 的 product_variant_id 欄位
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_id']);
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->onDelete('cascade');
            $table->unique(['cart_id', 'product_id', 'product_variant_id']);
        });
    }
};
