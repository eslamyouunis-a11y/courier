<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {

            if (!Schema::hasColumn('shipments', 'transfer_from_branch_id')) {
                $table->foreignId('transfer_from_branch_id')
                    ->nullable()
                    ->constrained('branches')
                    ->nullOnDelete()
                    ->after('current_courier_id');
            }

            if (!Schema::hasColumn('shipments', 'transfer_to_branch_id')) {
                $table->foreignId('transfer_to_branch_id')
                    ->nullable()
                    ->constrained('branches')
                    ->nullOnDelete()
                    ->after('transfer_from_branch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Drop FKs safely
            if (Schema::hasColumn('shipments', 'transfer_to_branch_id')) {
                $table->dropConstrainedForeignId('transfer_to_branch_id');
            }
            if (Schema::hasColumn('shipments', 'transfer_from_branch_id')) {
                $table->dropConstrainedForeignId('transfer_from_branch_id');
            }
        });
    }
};
