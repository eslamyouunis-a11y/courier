<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantFactory extends Factory
{
    protected $model = Merchant::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'branch_id' => Branch::factory(),
            'is_active' => true,
        ];
    }
}
