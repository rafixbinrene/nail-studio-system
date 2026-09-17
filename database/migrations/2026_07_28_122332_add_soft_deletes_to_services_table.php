<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This migration adds soft delete support to the services table.
|
| Purpose:
| - Deleted services will move to Trash instead of being permanently removed.
| - Services in Trash can be restored.
| - Services can be permanently deleted after 30 days.
|
| Defense explanation:
| This protects business data from accidental deletion and supports a
| recovery period before permanent removal.
*/

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('services', 'deleted_at')) {
            Schema::table('services', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('services', 'deleted_at')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};