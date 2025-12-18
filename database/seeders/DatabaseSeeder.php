<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Courier;
use App\Models\Merchant;
use App\Models\Governorate;
use App\Models\Area;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. حساب يونس (younis)
        User::create([
            'name' => 'younis',
            'email' => 'younis@app.com',
            'password' => Hash::make('12345678'),
        ]);

        // 2. قائمة المحافظات والداتا الموسعة
        $data = [
            'القاهرة' => ['مدينة نصر', 'المعادي', 'التجمع الخامس', 'مصر الجديدة', 'شبرا', 'وسط البلد', 'حلوان'],
            'الإسكندرية' => ['سموحة', 'ميامي', 'السيوف', 'محرم بك', 'العجمي', 'المنشية', 'لوران'],
            'الجيزة' => ['الدقي', 'المهندسين', 'الهرم', 'فيصل', 'أكتوبر', 'الشيخ زايد'],
            'القليوبية' => ['بنها', 'شبرا الخيمة', 'العبور', 'قليوب'],
            'المنوفية' => ['شبين الكوم', 'قويسنا', 'السادات', 'منوف'],
            'الشرقية' => ['الزقازيق', 'العاشر من رمضان', 'بلبيس'],
            'الغربية' => ['طنطا', 'المحلة الكبرى', 'كفر الزيات'],
            'الدقهلية' => ['المنصورة', 'طلخا', 'ميت غمر'],
        ];

        foreach ($data as $govName => $areas) {
            // إنشاء المحافظة
            $governorate = Governorate::create(['name' => $govName]);

            // 3. إنشاء الفرع وربطه بالمحافظة (حل المشكلة)
            $branch = Branch::create([
                'name' => $govName,
                'governorate_id' => $governorate->id, // الربط المفتقد
                'code' => 'BR-' . mt_rand(100, 999),
            ]);

            // 4. إنشاء المناطق
            foreach ($areas as $areaName) {
                Area::create([
                    'name' => $areaName,
                    'governorate_id' => $governorate->id
                ]);
            }

            // 5. إنشاء المناديب (بدون كابتن)
            Courier::create([
                'name' => 'أحمد ' . $govName,
                'phone' => '010' . mt_rand(11111111, 99999999),
                'branch_id' => $branch->id
            ]);

            Courier::create([
                'name' => 'محمود ' . $govName,
                'phone' => '011' . mt_rand(11111111, 99999999),
                'branch_id' => $branch->id
            ]);
        }

        // 6. إنشاء تجار أساسيين
        Merchant::create([
            'name' => 'متجر الأناقة',
            'phone' => '01555555555',
            'branch_id' => 1 // مربوط بأول فرع (القاهرة)
        ]);

        Merchant::create([
            'name' => 'تكنو ستور',
            'phone' => '01000000000',
            'branch_id' => 2 // مربوط بثاني فرع (الإسكندرية)
        ]);

        $this->command->info('تم إنشاء الداتا بنجاح: 8 محافظات، 8 فروع، 16 مندوب، وأكثر من 35 منطقة.');
    }
}
