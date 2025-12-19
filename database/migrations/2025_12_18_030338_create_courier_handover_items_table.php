<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courier_handover_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('handover_id')
                ->constrained('courier_handovers')
                ->cascadeOnDelete();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->enum('item_type', ['delivered', 'returned', 'postponed'])->default('delivered');
            $table->decimal('cod_amount', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['handover_id', 'shipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_handover_items');
    }
};
