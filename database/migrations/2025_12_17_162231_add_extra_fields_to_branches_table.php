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
    Schema::table('branches', function (Blueprint $table) {
        if (!Schema::hasColumn('branches', 'code')) {
            $table->string('code')->unique()->after('id');
        }
        if (!Schema::hasColumn('branches', 'wallet_balance')) {
            $table->decimal('wallet_balance', 15, 2)->default(0)->after('address');
        }
        if (!Schema::hasColumn('branches', 'manager_name')) {
            $table->string('manager_name')->nullable();
        }
        if (!Schema::hasColumn('branches', 'manager_phone')) {
            $table->string('manager_phone')->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            //
        });
    }
};
