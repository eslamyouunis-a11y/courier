<?php

namespace Database\Factories;

use App\Models\Courier;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'id_number' => $this->faker->numerify('#############'),
            'branch_id' => Branch::factory(),
            'commission_delivered' => 20,
            'commission_returned_paid' => 15,
            'commission_returned_sender' => 10,
            'is_active' => true,
        ];
    }
}
