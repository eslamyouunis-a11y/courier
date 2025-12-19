<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('merchant_payout_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payout_id')
                ->constrained('merchant_payouts')
                ->cascadeOnDelete();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['payout_id', 'shipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_payout_items');
    }
};
