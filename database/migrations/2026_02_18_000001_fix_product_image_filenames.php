<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix product image filenames that contain timestamps (uploaded via admin).
     * These files don't persist across Cloud Run deployments.
     * Set them to null so the frontend can show a placeholder instead of broken images.
     */
    public function up(): void
    {
        // Any image starting with a timestamp (digits followed by _) was uploaded via admin
        // and won't exist in the Docker image. Set to null.
        DB::table('products')
            ->whereRaw("image ~ '^[0-9]+_'")
            ->update(['image' => null]);
    }

    public function down(): void
    {
        // Cannot restore original filenames
    }
};
