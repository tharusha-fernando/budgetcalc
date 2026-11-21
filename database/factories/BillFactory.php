<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'vendor' => fake()->company(),
            'bill_number' => fake()->optional()->bothify('INV-####'),
            'bill_date' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
            'stated_total' => fake()->optional()->randomFloat(2, 100, 15000),
            'notes' => fake()->optional()->sentence(),
            'attachments' => [],
        ];
    }
}
