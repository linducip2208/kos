<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\UtilityReading;
use Illuminate\Database\Eloquent\Factories\Factory;

class UtilityReadingFactory extends Factory
{
    protected $model = UtilityReading::class;

    public function definition(): array
    {
        return [
            'room_id'          => Room::factory(),
            'type'             => 'electricity',
            'previous_reading' => 100,
            'current_reading'  => 150,
            'rate_per_unit'    => 1500,
            'amount'           => 75000,
            'reading_date'     => now()->toDateString(),
            'billing_period'   => now()->startOfMonth()->toDateString(),
            'added_to_invoice' => false,
        ];
    }
}
