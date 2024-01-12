<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostDetails;
use App\Models\User;
use App\Models\Category;
use App\Models\CategoriesPost;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(3)->create();
        Category::factory()->count(10)->create();
        Post::factory()->count(14)->create();
        PostDetails::factory()->count(24)->create();
        CategoriesPost::factory()->count(50)->create();





    }
}
