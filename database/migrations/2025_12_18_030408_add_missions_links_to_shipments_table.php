<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'courier_handover_id')) {
                $table->foreignId('courier_handover_id')->nullable()->after('current_courier_id')
                    ->constrained('courier_handovers')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'branch_deposit_id')) {
                $table->foreignId('branch_deposit_id')->nullable()->after('courier_handover_id')
                    ->constrained('branch_deposits')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'merchant_payout_id')) {
                $table->foreignId('merchant_payout_id')->nullable()->after('branch_deposit_id')
                    ->constrained('merchant_payouts')->nullOnDelete();
            }
            if (!Schema::hasColumn('shipments', 'merchant_return_mission_id')) {
                $table->foreignId('merchant_return_mission_id')->nullable()->after('merchant_payout_id')
                    ->constrained('merchant_return_missions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            foreach (['courier_handover_id','branch_deposit_id','merchant_payout_id','merchant_return_mission_id'] as $col) {
                if (Schema::hasColumn('shipments', $col)) {
                    $table->dropConstrainedForeignId($col);
                }
            }
        });
    }
};
