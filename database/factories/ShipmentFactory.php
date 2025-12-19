<?php

namespace Database\Factories;

use App\Models\Shipment;
use App\Models\Branch;
use App\Models\Courier;
use App\Models\Merchant;
use App\Models\Governorate;
use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition(): array
    {
        /**
         * Reference data (ثابتة – لا تتكرر)
         */
        $governorate = Governorate::firstOrCreate(
            ['name' => 'القاهرة'],
            [
                'shipping_cost' => 60,
                'is_active' => true,
            ]
        );

        $area = Area::firstOrCreate(
            [
                'name' => 'مدينة نصر',
                'governorate_id' => $governorate->id,
            ]
        );

        /**
         * Operational entities
         */
        $branch = Branch::factory()->create([
            'governorate_id' => $governorate->id,
        ]);

        $courier = Courier::factory()->create([
            'branch_id' => $branch->id,
        ]);

        $merchant = Merchant::factory()->create([
            'branch_id' => $branch->id,
        ]);

        return [
            // Identifiers
            'tracking_number'  => $this->faker->unique()->numerify('########'),

            // Location
            'branch_id'        => $branch->id,
            'governorate_id'   => $governorate->id,
            'area_id'          => $area->id,

            // Relations
            'courier_id'       => $courier->id,
            'merchant_id'      => $merchant->id,

            // Customer info (NOT NULL)
            'customer_name'    => $this->faker->name(),
            'customer_phone'   => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->address(),

            // Financials
            'amount'           => 1000,
            'shipping_fees'    => 60,

            // Status
            'status'           => Shipment::STATUS_DELIVERED,
            'current_location' => Shipment::LOCATION_BRANCH,
        ];
    }
}
