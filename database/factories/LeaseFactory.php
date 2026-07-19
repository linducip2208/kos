<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\Occupant;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaseFactory extends Factory
{
    protected $model = Lease::class;

    public function definition(): array
    {
        $start = now()->subMonths(2);
        return [
            'room_id'       => Room::factory(),
            'occupant_id'   => Occupant::factory(),
            'lease_number'  => 'LSE-' . $this->faker->unique()->numerify('######'),
            'start_date'    => $start->toDateString(),
            'end_date'      => $start->addYear()->toDateString(),
            'price'         => 1000000,
            'deposit'       => 1000000,
            'billing_cycle' => 'monthly',
            'billing_date'  => 1,
            'status'        => 'active',
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function expired(): static
    {
        return $this->state([
            'status'     => 'expired',
            'start_date' => now()->subYears(2)->toDateString(),
            'end_date'   => now()->subYear()->toDateString(),
        ]);
    }
}
