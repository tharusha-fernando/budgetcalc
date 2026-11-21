<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Project;
use App\Models\RestorationPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestorationPayment>
 */
class RestorationPaymentFactory extends Factory
{
    protected $model = RestorationPayment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'amount' => fake()->randomFloat(2, 500, 25000),
            'method' => PaymentMethod::Cash,
            'paid_on' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
            'attachments' => [],
        ];
    }
}
