<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
|--------------------------------------------------------------------------
| App Notifications Table
|
| Purpose:
| - Stores in-app notifications for admin, staff, and customer users.
| - Supports unread/read notification status.
| - Stores optional link for quick navigation.
|
| Defense explanation:
| This table allows the system to notify users about important booking,
| account, feedback, and security events inside the web application.
|--------------------------------------------------------------------------
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recipient_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('recipient_role')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->string('type')->default('general');

            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['recipient_user_id', 'read_at']);
            $table->index(['recipient_role', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};