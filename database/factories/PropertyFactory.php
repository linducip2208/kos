<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'name'      => $this->faker->company . ' Kos',
            'address'   => $this->faker->streetAddress,
            'city'      => $this->faker->city,
            'province'  => 'Jawa Barat',
            'is_active' => true,
        ];
    }
}
