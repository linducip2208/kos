<?php

namespace Database\Factories;

use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactSubmissionFactory extends Factory
{
    protected $model = ContactSubmission::class;

    public function definition(): array
    {
        return [
            'property_id' => null,
            'name'        => $this->faker->name,
            'phone'       => '08' . $this->faker->numerify('##########'),
            'email'       => $this->faker->safeEmail,
            'subject'     => $this->faker->sentence(4),
            'message'     => $this->faker->paragraph(2),
            'status'      => 'new',
            'ip_address'  => $this->faker->ipv4,
            'tenant_id'   => null,
        ];
    }

    public function read(): static    { return $this->state(['status' => 'read']); }
    public function replied(): static { return $this->state(['status' => 'replied', 'replied_at' => now()]); }
}
