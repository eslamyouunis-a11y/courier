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
    Schema::create('merchants', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique(); // كود التاجر التلقائي
        $table->string('name'); // اسم المتجر / التاجر
        $table->string('phone')->unique();
        $table->string('address')->nullable();

        // المحفظة 1: مستحقات التاجر (فلوس الطرود اللي اتسلمت ومحتاجه تتصرف له)
        $table->decimal('cod_balance', 15, 2)->default(0);

        // المحفظة 2: رصيد الشحن (رصيد التاجر شاحنه مقدماً لخصم مصاريف الشحن)
        $table->decimal('prepaid_balance', 15, 2)->default(0);

        // الربط الإلزامي بالفرع
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
        Schema::dropIfExists('merchants');
    }
};
