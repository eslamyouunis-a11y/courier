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
    Schema::create('governorates', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // اسم المحافظة (القاهرة، الإسكندرية...)
        $table->decimal('shipping_cost', 10, 2)->default(0); // سعر الشحن للمحافظة دي
        $table->boolean('is_active')->default(true); // هل بنشحن للمحافظة دي حالياً؟
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};
