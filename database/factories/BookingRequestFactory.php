<?php

namespace Database\Factories;

use App\Models\BookingRequest;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingRequestFactory extends Factory
{
    protected $model = BookingRequest::class;

    public function definition(): array
    {
        return [
            'property_id'     => Property::factory(),
            'name'            => $this->faker->name,
            'phone'           => '08' . $this->faker->numerify('#########'),
            'email'           => $this->faker->safeEmail,
            'whatsapp'        => '08' . $this->faker->numerify('#########'),
            'desired_move_in' => now()->addDays(14)->toDateString(),
            'billing_cycle'   => 'monthly',
            'status'          => 'pending',
        ];
    }
}
