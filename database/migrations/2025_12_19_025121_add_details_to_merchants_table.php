<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {

            // 1. فحص البريد الإلكتروني
            if (!Schema::hasColumn('merchants', 'email')) {
                $table->string('email')->nullable()->after('name');
            }

            // 2. فحص المحافظة
            if (!Schema::hasColumn('merchants', 'governorate_id')) {
                $table->foreignId('governorate_id')->nullable()->after('branch_id')->constrained('governorates')->nullOnDelete();
            }

            // 3. فحص المنطقة
            if (!Schema::hasColumn('merchants', 'area_id')) {
                $table->foreignId('area_id')->nullable()->after('governorate_id')->constrained('areas')->nullOnDelete();
            }

            // 4. فحص نسبة المرتجع (سبب المشكلة)
            if (!Schema::hasColumn('merchants', 'return_shipping_percentage')) {
                $table->integer('return_shipping_percentage')->default(100)->after('address');
            }

            // 5. فحص أيام التسوية
            if (!Schema::hasColumn('merchants', 'settlement_days')) {
                $table->integer('settlement_days')->default(1)->after('return_shipping_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            // حذف الأعمدة لو وجدت
            $columns = ['email', 'governorate_id', 'area_id', 'return_shipping_percentage', 'settlement_days'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('merchants', $column)) {
                    if (in_array($column, ['governorate_id', 'area_id'])) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
