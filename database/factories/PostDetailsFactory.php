<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostDetails>
 */
class PostDetailsFactory extends Factory
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
                // Pobierz ID losowego postu
                return Post::inRandomOrder()->first()->id;
            },
            'class_name' => $this->faker->boolean ? 'test' : 'test1',
            'image' => $this->faker->imageUrl(), // Możesz dostosować generowanie losowego URL obrazu
            'text' => $this->faker->paragraph,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
