<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'user_id')) {
                    $table->foreignId('user_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('users')
                        ->nullOnDelete();

                    $table->unique('user_id');
                }

                if (!Schema::hasColumn('customers', 'address')) {
                    $table->text('address')->nullable()->after('phone_number');
                }

                if (!Schema::hasColumn('customers', 'status')) {
                    $table->string('status')->default('active')->after('address');
                }
            });
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (!Schema::hasColumn('appointments', 'booking_type')) {
                    $table->string('booking_type')->default('regular')->after('status');
                }

                if (!Schema::hasColumn('appointments', 'follow_up_from')) {
                    $table->string('follow_up_from')->nullable()->after('booking_type');
                }

                if (!Schema::hasColumn('appointments', 'follow_up_reason')) {
                    $table->text('follow_up_reason')->nullable()->after('follow_up_from');
                }

                if (!Schema::hasColumn('appointments', 'cancellation_reason')) {
                    $table->text('cancellation_reason')->nullable()->after('follow_up_reason');
                }

                if (!Schema::hasColumn('appointments', 'cancelled_by')) {
                    $table->foreignId('cancelled_by')
                        ->nullable()
                        ->after('cancellation_reason')
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('appointments', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
                }

                if (!Schema::hasColumn('appointments', 'status_updated_by')) {
                    $table->foreignId('status_updated_by')
                        ->nullable()
                        ->after('cancelled_at')
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('appointments', 'status_updated_at')) {
                    $table->timestamp('status_updated_at')->nullable()->after('status_updated_by');
                }
            });
        }

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('audit_logs', 'user_id')) {
                    $table->foreignId('user_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn('audit_logs', 'module')) {
                    $table->string('module')->nullable()->after('action');
                }

                if (!Schema::hasColumn('audit_logs', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('description');
                }

                if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                }
            });
        }

        if (!Schema::hasTable('appointment_services')) {
            Schema::create('appointment_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('appointment_id')
                    ->constrained('appointments')
                    ->cascadeOnDelete();
                $table->foreignId('service_id')
                    ->constrained('services')
                    ->cascadeOnDelete();
                $table->decimal('price', 10, 2)->default(0);
                $table->unsignedInteger('duration')->default(0);
                $table->timestamps();

                $table->unique(['appointment_id', 'service_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_services');

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('audit_logs', 'user_id')) {
                    $table->dropConstrainedForeignId('user_id');
                }

                foreach (['module', 'ip_address', 'user_agent'] as $column) {
                    if (Schema::hasColumn('audit_logs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if (Schema::hasColumn('appointments', 'cancelled_by')) {
                    $table->dropConstrainedForeignId('cancelled_by');
                }

                if (Schema::hasColumn('appointments', 'status_updated_by')) {
                    $table->dropConstrainedForeignId('status_updated_by');
                }

                foreach ([
                    'booking_type',
                    'follow_up_from',
                    'follow_up_reason',
                    'cancellation_reason',
                    'cancelled_at',
                    'status_updated_at',
                ] as $column) {
                    if (Schema::hasColumn('appointments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'user_id')) {
                    $table->dropConstrainedForeignId('user_id');
                }

                foreach (['address', 'status'] as $column) {
                    if (Schema::hasColumn('customers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};