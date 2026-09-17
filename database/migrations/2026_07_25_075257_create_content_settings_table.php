<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| This migration creates the content_settings table.
|
| Purpose:
| - Allows the admin to update website content without editing code.
| - Stores salon name, hero text, about section, contact details,
|   business hours, and announcement message.
|
| Defense explanation:
| This supports the Content Management module of the admin side.
| It improves maintainability because business content can be updated
| dynamically from the system.
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_settings', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Basic public website identity.
            |--------------------------------------------------------------------------
            */
            $table->string('salon_name')->default('Nail Studio & Beauty');
            $table->string('tagline')->default('Your Beauty, Our Priority');

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Homepage hero content.
            |--------------------------------------------------------------------------
            */
            $table->string('hero_title')->default('Welcome to Nail Studio & Beauty');
            $table->text('hero_subtitle')->nullable();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | About section content.
            |--------------------------------------------------------------------------
            */
            $table->string('about_title')->default('About Nail Studio & Beauty');
            $table->text('about_description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Contact and business information.
            |--------------------------------------------------------------------------
            */
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->text('opening_hours')->nullable();

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Announcement section.
            | The admin can turn this on/off from Content Management.
            |--------------------------------------------------------------------------
            */
            $table->string('announcement_title')->nullable();
            $table->text('announcement_message')->nullable();
            $table->enum('announcement_status', ['active', 'inactive'])->default('inactive');

            /*
            |--------------------------------------------------------------------------
            | NS BEAUTY COMMENT:
            | Tracks which admin last updated the content.
            |--------------------------------------------------------------------------
            */
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_settings');
    }
};