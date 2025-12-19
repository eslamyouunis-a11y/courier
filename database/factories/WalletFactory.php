<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'currency' => 'EGP',
            'balance' => 0,
        ];
    }

    /**
     * Attach wallet to any owner (Branch / Courier / Merchant)
     */
    public function forOwner(Model $owner): self
    {
        return $this->state(function () use ($owner) {
            return [
                'owner_type' => get_class($owner),
                'owner_id'   => $owner->id,
            ];
        });
    }
}
