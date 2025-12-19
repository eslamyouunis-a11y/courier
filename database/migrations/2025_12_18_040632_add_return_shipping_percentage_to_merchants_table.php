<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->decimal('return_shipping_percentage', 5, 2)
                ->default(100)
                ->comment('Percentage of shipping fees charged on return to sender');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn('return_shipping_percentage');
        });
    }
};
