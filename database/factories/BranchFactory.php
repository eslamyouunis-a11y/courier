<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $governorate = Governorate::firstOrCreate(
            ['name' => 'القاهرة'],
            ['shipping_cost' => 60, 'is_active' => true]
        );

        return [
            'name' => 'فرع القاهرة',
            'phone' => '01000000000',
            'address' => 'عنوان الفرع',
            'governorate_id' => $governorate->id,
            'is_active' => true,
        ];
    }
}
