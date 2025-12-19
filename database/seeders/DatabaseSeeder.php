<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\{User, Branch, Courier, Merchant, Wallet, Governorate, Area};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // 1️⃣ حساب الإدارة (Admin)
            $admin = User::updateOrCreate(
                ['email' => 'younis@app.com'],
                ['name' => 'Eslam Younis', 'password' => Hash::make('12345678')]
            );

            // 2️⃣ التأكد من وجود محفظة للشركة (Company Wallet)
            Wallet::firstOrCreate(
                ['owner_type' => 'company'],
                ['balance' => 0]
            );

            // 3️⃣ توليد محافظ لجميع الفروع الموجودة
            $this->command->info('⏳ جاري إنشاء محافظ للفروع...');
            Branch::all()->each(fn ($branch) => $this->createWalletIfNotExists($branch));

            // 4️⃣ توليد محافظ لجميع المناديب
            $this->command->info('⏳ جاري إنشاء محافظ للمناديب...');
            Courier::all()->each(fn ($courier) => $this->createWalletIfNotExists($courier));

            // 5️⃣ توليد محافظ لجميع التجار
            $this->command->info('⏳ جاري إنشاء محافظ للتجار...');
            Merchant::all()->each(fn ($merchant) => $this->createWalletIfNotExists($merchant));

            DB::commit();
            $this->command->info('✅ تم إنشاء محافظ لكل الناس بنجاح! السيستم جاهز للعمليات المالية الآن.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ خطأ في السيدر: ' . $e->getMessage());
        }
    }

    /**
     * دالة مساعدة لإنشاء محفظة فقط إذا لم تكن موجودة
     */
    private function createWalletIfNotExists($model): void
    {
        Wallet::firstOrCreate([
            'owner_type' => get_class($model),
            'owner_id' => $model->id,
        ], [
            'balance' => 0,
        ]);
    }
}
