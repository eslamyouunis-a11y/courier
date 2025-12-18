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
    Schema::create('couriers', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->string('phone')->unique();
        $table->string('id_number')->nullable();

        // المحفظة الأولى: مبالغ التحصيل (فلوس الطرود)
        $table->decimal('cod_wallet', 15, 2)->default(0);

        // المحفظة الثانية: عمولات المندوب (فلوسه الشخصية)
        $table->decimal('commission_wallet', 15, 2)->default(0);

        $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
