<?php

namespace Database\Factories;

use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceRequestFactory extends Factory
{
    protected $model = MaintenanceRequest::class;

    public function definition(): array
    {
        return [
            'room_id'     => Room::factory(),
            'occupant_id' => Occupant::factory(),
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high']),
            'status'      => 'open',
        ];
    }
}
