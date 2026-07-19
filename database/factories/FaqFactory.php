<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'property_id' => null,
            'question'    => $this->faker->sentence(6, true) . '?',
            'answer'      => $this->faker->paragraph(2),
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
