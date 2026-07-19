<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'property_id' => null,
            'name'        => $this->faker->name,
            'occupation'  => $this->faker->randomElement(['Mahasiswa', 'Karyawan', 'Wirausaha', 'Freelancer']),
            'avatar'      => null,
            'rating'      => $this->faker->numberBetween(4, 5),
            'content'     => $this->faker->paragraph(2),
            'order'       => $this->faker->numberBetween(0, 10),
            'is_active'   => true,
            'tenant_id'   => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
