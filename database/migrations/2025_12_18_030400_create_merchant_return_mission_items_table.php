<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('merchant_return_mission_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mission_id')
                ->constrained('merchant_return_missions')
                ->cascadeOnDelete();

            $table->foreignId('shipment_id')
                ->constrained('shipments')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['mission_id', 'shipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_return_mission_items');
    }
};
