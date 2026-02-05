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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            //constrained 自動關聯到category的id
            // onDelete('cascade') 當分類被刪除時，該分類下的商品也會被刪除
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');//品牌
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price',10,2);//表示總共10位數 小數點到第二位
            $table->decimal('original_price',10,2); //原價 用於特價顯示
            $table->string('image')->nullable();
            $table->string('tag')->nullable();//新品 特價 熱銷
            $table->integer('stock')->default(0);//庫存數量 預設為0
            $table->boolean('is_active')->default(true);//是否上架
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
