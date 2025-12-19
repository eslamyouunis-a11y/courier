<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // SQLite: enum = string عمليًا، مفيش تغيير مطلوب
        if ($driver === 'sqlite') {
            return;
        }

        // MySQL: تعديل ENUM لإضافة cancelled
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE merchant_payouts MODIFY status ENUM('open','paid','cancelled') NOT NULL DEFAULT 'open'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE merchant_payouts MODIFY status ENUM('open','paid') NOT NULL DEFAULT 'open'");
        }
    }
};
