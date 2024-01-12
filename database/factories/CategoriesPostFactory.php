<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CategoriesPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => function () {
                return Post::inRandomOrder()->first()->id;
            },
            'category_id' => function () {
                return Category::inRandomOrder()->first()->id;
            },
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
