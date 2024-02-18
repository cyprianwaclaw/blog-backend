<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
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
            'user_id' => function () {
                // Pobierz ID losowego postu
                return User::inRandomOrder()->first()->id;
            },
            'text' => $this->faker->paragraph,
            'relaction'=> Comment::COMMENT_TYPE_ENUM[array_rand(Comment::COMMENT_TYPE_ENUM)],
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
