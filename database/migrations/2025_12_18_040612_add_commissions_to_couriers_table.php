<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->decimal('commission_delivered', 8, 2)->default(0)->after('phone');
            $table->decimal('commission_returned_paid', 8, 2)->default(0)->after('commission_delivered');
            $table->decimal('commission_returned_sender', 8, 2)->default(0)->after('commission_returned_paid');
        });
    }

    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropColumn([
                'commission_delivered',
                'commission_returned_paid',
                'commission_returned_sender',
            ]);
        });
    }
};
