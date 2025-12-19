<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('merchant_return_missions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')->constrained('merchants')->cascadeOnDelete();

            $table->enum('status', ['open', 'completed'])->default('open');

            $table->unsignedInteger('shipments_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_return_missions');
    }
};
