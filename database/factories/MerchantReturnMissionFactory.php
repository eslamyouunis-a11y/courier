<?php

namespace Database\Factories;

use App\Models\MerchantReturnMission;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantReturnMissionFactory extends Factory
{
    protected $model = MerchantReturnMission::class;

    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'status'      => MerchantReturnMission::STATUS_OPEN,
            'created_by'  => User::factory(),
        ];
    }
}
