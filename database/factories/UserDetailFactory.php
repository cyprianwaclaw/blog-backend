<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\userDetail>
 */
class userDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::all();
        return [
            'user_id' => $users->random()->id,
            'about_user' => $this->faker->boolean ? null : $this->faker->text,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
