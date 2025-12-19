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
        Schema::table('merchant_shipping_fees', function (Blueprint $table) {
            // إضافة عمود المنطقة وربطه بجدول areas
            $table->foreignId('area_id')
                  ->nullable()                // مسموح يكون فارغ (للمحافظة كلها)
                  ->after('to_governorate_id') // ترتيبه في الجدول
                  ->constrained('areas')      // الربط
                  ->nullOnDelete();           // لو المنطقة اتمسحت، الحقل ده يبقى null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_shipping_fees', function (Blueprint $table) {
            // حذف العلاقة أولاً ثم العمود في حالة التراجع
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });
    }
};
