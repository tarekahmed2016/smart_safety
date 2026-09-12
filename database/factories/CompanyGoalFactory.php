<?php

namespace Database\Factories;

use App\Models\CompanyGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyGoal>
 */
class CompanyGoalFactory extends Factory
{
    protected $model = CompanyGoal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'text_ar' => fake()->sentence(),
            'text_en' => fake()->sentence(),
            'ordering' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
