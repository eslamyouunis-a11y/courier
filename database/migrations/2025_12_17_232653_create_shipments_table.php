<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();

            // الأطراف الأساسية (صاحب الشحنة والمنشأ)
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete(); // فرع المنشأ
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete(); // المندوب المخصص

            // الموقع الحالي (Current Status) - لفلترة القبول والأوامر
            $table->foreignId('current_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('current_courier_id')->nullable()->constrained('couriers')->nullOnDelete();

            // الموقع الجغرافي للعميل
            $table->foreignId('governorate_id')->constrained();
            $table->foreignId('area_id')->constrained();

            // الحالات
            $table->string('status')->default('saved'); // محفوظة، قيد التنفيذ، مرتجعة
            $table->string('sub_status')->nullable();

            // التواريخ الذكية (زي الصورة)
            $table->timestamp('accepted_at')->nullable();      // تاريخ القبول في الفرع
            $table->timestamp('executed_at')->nullable();      // تاريخ التنفيذ النهائي
            $table->timestamp('delivered_at')->nullable();     // تاريخ التسليم للعميل
            $table->timestamp('last_deferred_at')->nullable(); // تاريخ آخر تأجيل
            $table->date('deferred_to_date')->nullable();      // التأجيل إلى
            $table->date('expected_delivery_date')->nullable(); // الموعد المتوقع

            // بيانات العميل والماليات
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_phone_2')->nullable();
            $table->text('customer_address');
            $table->decimal('amount', 10, 2)->default(0);        // سعر المنتج
            $table->decimal('shipping_fees', 10, 2)->default(0); // مصاريف الشحن
            $table->decimal('total_amount', 10, 2)->default(0);  // الإجمالي
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('shipments');
    }
};
