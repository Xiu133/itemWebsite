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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 標籤名稱 如:新品、特價、熱銷
            $table->string('slug')->unique(); // URL友善的識別碼
            $table->string('color')->default('#3B82F6'); // badge顏色 (hex color)
            $table->string('icon')->nullable(); // 圖示class或emoji
            $table->boolean('is_active')->default(true); // 是否啟用
            $table->integer('sort_order')->default(0); // 排序
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
