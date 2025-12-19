<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            // polymorphic owner: Courier / Branch / Merchant / Company
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id')->nullable(); // company can be null

            $table->string('currency', 3)->default('EGP');

            // cached balance (optional – الحقيقة في wallet_transactions)
            $table->decimal('balance', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
