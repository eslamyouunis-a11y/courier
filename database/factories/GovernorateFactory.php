<?php

namespace Database\Factories;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

class GovernorateFactory extends Factory
{
    protected $model = Governorate::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'القاهرة',
            'الجيزة',
            'الإسكندرية',
            'الدقهلية',
            'الشرقية',
            'الغربية',
            'المنوفية',
            'البحيرة',
            'كفر الشيخ',
            'دمياط',
        ]);

        return [
            // المهم هنا 👇
            'name' => $name,
            'shipping_cost' => 60,
            'is_active' => true,
        ];
    }
}
