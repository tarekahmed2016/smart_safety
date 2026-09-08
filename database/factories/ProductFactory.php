<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameEn = fake()->unique()->words(3, true);

        return [
            'name_ar' => fake()->words(3, true),
            'name_en' => $nameEn,
            'slug' => Str::slug($nameEn).'-'.fake()->unique()->numerify('###'),
            'description_ar' => fake()->optional()->sentence(),
            'description_en' => fake()->optional()->sentence(),
            'ordering' => fake()->numberBetween(0, 20),
            'is_active' => true,
            'show_on_homepage' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
