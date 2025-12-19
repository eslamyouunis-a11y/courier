<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // إضافة عمود تاريخ التوصيل المتوقع لو مش موجود
            if (!Schema::hasColumn('shipments', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('expected_delivery_date');
        });
    }
};
