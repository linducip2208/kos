<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Lease;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'lease_id'       => Lease::factory(),
            'invoice_number' => 'INV-' . $this->faker->unique()->numerify('######'),
            'period_start'   => now()->startOfMonth()->toDateString(),
            'period_end'     => now()->endOfMonth()->toDateString(),
            'due_date'       => now()->addDays(7)->toDateString(),
            'base_amount'    => 1000000,
            'total'          => 1000000,
            'status'         => 'sent',
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state([
            'status'   => 'overdue',
            'due_date' => now()->subDays(5)->toDateString(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }
}
