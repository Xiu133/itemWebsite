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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email'); // 電話
            $table->string('avatar')->nullable()->after('phone'); // 頭像
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('avatar'); // 性別
            $table->date('birthday')->nullable()->after('gender'); // 生日
            $table->enum('role', ['admin', 'customer'])->default('customer')->after('birthday'); // 角色
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'avatar', 'gender', 'birthday', 'role']);
        });
    }
};
