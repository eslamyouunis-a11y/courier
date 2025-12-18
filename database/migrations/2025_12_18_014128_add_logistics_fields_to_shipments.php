<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // إضافة حقول التأجيل مع التأكد من عدم وجودها
            if (!Schema::hasColumn('shipments', 'defer_reason')) {
                $table->string('defer_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('shipments', 'defers_count')) {
                $table->integer('defers_count')->default(0)->after('defer_reason');
            }
            if (!Schema::hasColumn('shipments', 'last_deferred_at')) {
                $table->timestamp('last_deferred_at')->nullable()->after('defers_count');
            }

            // إضافة حقول المرتجع
            if (!Schema::hasColumn('shipments', 'return_reason')) {
                $table->string('return_reason')->nullable()->after('last_deferred_at');
            }
            if (!Schema::hasColumn('shipments', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('return_reason');
            }

            // إضافة علاقة الفرع والمندوب
            if (!Schema::hasColumn('shipments', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            }
            if (!Schema::hasColumn('shipments', 'courier_id')) {
                $table->foreignId('courier_id')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['defer_reason', 'defers_count', 'last_deferred_at', 'return_reason', 'paid_amount']);
        });
    }
};
