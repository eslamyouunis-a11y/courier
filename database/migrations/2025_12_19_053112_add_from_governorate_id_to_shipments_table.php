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
        // إضافة عمود محافظة الراسل لربطه بحساب مصاريف الشحن
        $table->foreignId('from_governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('shipments', function (Blueprint $table) {
        $table->dropColumn('from_governorate_id');
    });
}
};
