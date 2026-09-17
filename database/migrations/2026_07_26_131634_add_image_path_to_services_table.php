<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This migration adds an image_path column to the services table.
|
| Purpose:
| - Allows each service to have its own uploaded image.
| - The uploaded image will appear on the public welcome page.
|
| Defense explanation:
| This supports the Service Management module because the admin can update
| service information and service visuals without editing source code.
*/

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('services', 'image_path')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('services', 'image_path')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
};