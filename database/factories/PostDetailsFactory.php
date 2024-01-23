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
        $classNames = ['title', 'list', 'paragraph', 'class4', 'class5'];
        return [
            'post_id' => function () {
                // Pobierz ID losowego postu
                return Post::inRandomOrder()->first()->id;
            },
            // 'index' => $this->faker->randomElement($classNames),
            'class_name' => $this->faker->randomElement($classNames),
            'image' =>  $this->faker->boolean ? null : $this->faker->imageUrl(),
            'text' => $this->faker->boolean ? null : $this->faker->text,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
