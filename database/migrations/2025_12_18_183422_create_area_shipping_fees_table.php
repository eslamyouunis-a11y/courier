<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_shipping_fees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipping_fee_id')
                ->constrained('shipping_fees')
                ->cascadeOnDelete();

            $table->foreignId('area_id')
                ->constrained('areas')
                ->cascadeOnDelete();

            // Delivery
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->enum('delivery_fee_type', ['fixed', 'percent'])->default('fixed');

            // Return to Sender
            $table->decimal('return_fee', 10, 2)->default(0);
            $table->enum('return_fee_type', ['fixed', 'percent'])->default('fixed');

            // Cancellation
            $table->decimal('cancel_fee', 10, 2)->default(0);
            $table->enum('cancel_fee_type', ['fixed', 'percent'])->default('fixed');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['shipping_fee_id', 'area_id'],
                'area_shipping_fees_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_shipping_fees');
    }
};
