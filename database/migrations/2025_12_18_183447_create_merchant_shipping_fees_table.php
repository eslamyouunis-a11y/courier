<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_shipping_fees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')
                ->constrained('merchants')
                ->cascadeOnDelete();

            $table->foreignId('from_governorate_id')
                ->constrained('governorates')
                ->cascadeOnDelete();

            $table->foreignId('to_governorate_id')
                ->constrained('governorates')
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
                ['merchant_id', 'from_governorate_id', 'to_governorate_id'],
                'merchant_shipping_fees_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_shipping_fees');
    }
};
