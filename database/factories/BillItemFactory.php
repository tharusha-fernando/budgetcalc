<?php

namespace Database\Factories;

use App\Enums\BillItemCategory;
use App\Enums\BillItemStatus;
use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillItem>
 */
class BillItemFactory extends Factory
{
    protected $model = BillItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'description' => fake()->words(3, true),
            'category' => fake()->randomElement(BillItemCategory::cases()),
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit_price' => fake()->randomFloat(2, 50, 5000),
            'status' => BillItemStatus::Active,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (): array => [
            'status' => BillItemStatus::Returned,
            'settled_at' => now()->toDateString(),
        ]);
    }

    public function waived(): static
    {
        return $this->state(fn (): array => [
            'status' => BillItemStatus::Waived,
            'settled_at' => now()->toDateString(),
        ]);
    }
}
