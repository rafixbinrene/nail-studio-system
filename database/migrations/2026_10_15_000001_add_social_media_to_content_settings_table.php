
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT: Social Media Database Migration
|--------------------------------------------------------------------------
| Adds TikTok and WhatsApp contact information to Content Management.
|
| Existing business information, announcements, and image paths
| remain unchanged.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Add Missing Social Media Columns
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('content_settings', 'tiktok_url')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('tiktok_url', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('content_settings', 'whatsapp_url')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('whatsapp_url', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('content_settings', 'whatsapp_number')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('whatsapp_number', 30)->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | NS BEAUTY COMMENT: Remove Added Columns on Rollback
        |--------------------------------------------------------------------------
        */

        foreach ([
            'tiktok_url',
            'whatsapp_url',
            'whatsapp_number',
        ] as $column) {
            if (Schema::hasColumn('content_settings', $column)) {
                Schema::table('content_settings', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
