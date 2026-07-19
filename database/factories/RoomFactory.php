<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'property_id'    => Property::factory(),
            'room_number'    => $this->faker->unique()->numerify('##'),
            'floor'          => $this->faker->numberBetween(1, 4),
            'price_monthly'  => $this->faker->randomElement([500000, 750000, 1000000, 1500000]),
            'status'         => 'available',
            'is_active'      => true,
        ];
    }

    public function occupied(): static
    {
        return $this->state(['status' => 'occupied']);
    }

    public function available(): static
    {
        return $this->state(['status' => 'available']);
    }
}
