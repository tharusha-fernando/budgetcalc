<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Restoration',
            'vehicle' => fake()->year().' '.fake()->lastName().' Classic',
            'restoration_guy' => fake()->name(),
            'agreed_amount' => fake()->randomFloat(2, 50000, 250000),
            'cash_on_hand' => fake()->optional()->randomFloat(2, 1000, 50000),
            'cash_to_load' => fake()->optional()->randomFloat(2, 1000, 30000),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
