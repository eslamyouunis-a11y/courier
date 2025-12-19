<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('branch_deposit_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('deposit_id')
                ->constrained('branch_deposits')
                ->cascadeOnDelete();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->enum('item_type', ['delivered', 'returned_paid'])->default('delivered');
            $table->decimal('amount', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['deposit_id', 'shipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_deposit_items');
    }
};
