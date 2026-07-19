<?php

namespace Database\Factories;

use App\Models\Occupant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class OccupantFactory extends Factory
{
    protected $model = Occupant::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->name,
            'phone'         => '08' . $this->faker->unique()->numerify('#########'),
            'email'         => $this->faker->unique()->safeEmail,
            'whatsapp'      => '08' . $this->faker->numerify('#########'),
            'id_number'     => $this->faker->numerify('################'),
            'portal_active' => false,
        ];
    }

    public function withPortalAccess(string $password = 'password123'): static
    {
        return $this->state([
            'portal_password' => Hash::make($password),
            'portal_active'   => true,
        ]);
    }
}
