<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Assign all unowned products (from seeder) to the first registered user.
     */
    public function up(): void
    {
        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        if ($firstUserId) {
            DB::table('products')
                ->whereNull('user_id')
                ->update(['user_id' => $firstUserId]);
        }
    }

    public function down(): void
    {
        // Cannot reliably determine which were originally null
    }
};
