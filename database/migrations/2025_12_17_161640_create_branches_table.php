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
    Schema::create('branches', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // اسم الفرع (فرع المهندسين، فرع طنطا)
        $table->string('phone')->nullable(); // رقم تليفون الفرع
        $table->string('address')->nullable(); // عنوان الفرع بالتفصيل
        $table->foreignId('governorate_id')->constrained()->cascadeOnDelete(); // تابع لانو محافظة
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
