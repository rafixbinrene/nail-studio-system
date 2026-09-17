<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This migration adds uploadable image fields to the content_settings table.
|
| Purpose:
| - Allows admin to upload homepage slideshow images.
| - Allows admin to upload a studio image for the welcome page.
|
| Defense explanation:
| This supports Content Management by allowing the admin to update website
| visuals without editing source code.
*/

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('content_settings', 'hero_slide_1_image')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('hero_slide_1_image')->nullable()->after('hero_subtitle');
            });
        }

        if (!Schema::hasColumn('content_settings', 'hero_slide_2_image')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('hero_slide_2_image')->nullable()->after('hero_slide_1_image');
            });
        }

        if (!Schema::hasColumn('content_settings', 'hero_slide_3_image')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('hero_slide_3_image')->nullable()->after('hero_slide_2_image');
            });
        }

        if (!Schema::hasColumn('content_settings', 'hero_slide_4_image')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('hero_slide_4_image')->nullable()->after('hero_slide_3_image');
            });
        }

        if (!Schema::hasColumn('content_settings', 'hero_slide_5_image')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('hero_slide_5_image')->nullable()->after('hero_slide_4_image');
            });
        }

        if (!Schema::hasColumn('content_settings', 'studio_image_path')) {
            Schema::table('content_settings', function (Blueprint $table) {
                $table->string('studio_image_path')->nullable()->after('opening_hours');
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'hero_slide_1_image',
            'hero_slide_2_image',
            'hero_slide_3_image',
            'hero_slide_4_image',
            'hero_slide_5_image',
            'studio_image_path',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('content_settings', $column)) {
                Schema::table('content_settings', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};