<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            // الربط مع جدول المحافظات
            $table->foreignId('governorate_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            // سعر شحن إضافي لو المنطقة دي سعرها مختلف عن المحافظة (nullable)
            $table->decimal('override_shipping_cost', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
