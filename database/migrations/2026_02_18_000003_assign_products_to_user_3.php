<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereNull('user_id')
            ->update(['user_id' => 3]);
    }

    public function down(): void
    {
        DB::table('products')
            ->where('user_id', 3)
            ->update(['user_id' => null]);
    }
};
