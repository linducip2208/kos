<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'property_id'           => Property::factory(),
            'name'                  => $this->faker->randomElement(['Standard', 'Deluxe', 'VIP', 'Economy']),
            'description'           => $this->faker->sentence(10),
            'size_sqm'              => $this->faker->randomFloat(2, 8, 25),
            'base_price_daily'      => null,
            'base_price_weekly'     => null,
            'base_price_monthly'    => $this->faker->numberBetween(800000, 3000000),
            'base_price_quarterly'  => null,
            'base_price_yearly'     => null,
            'facilities'            => ['AC', 'Kasur', 'Lemari'],
            'photos'                => [],
            'max_occupants'         => 1,
        ];
    }
}
