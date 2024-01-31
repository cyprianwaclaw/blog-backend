<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostDetails;
use App\Models\User;
use App\Models\Category;
use App\Models\CategoriesPost;
use App\Models\Comment;
use App\Models\SavedPost;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->count(10)->create();
        Category::factory()->count(40)->create();
        Post::factory()->count(100)->create();
        PostDetails::factory()->count(400)->create();
        CategoriesPost::factory()->count(2500)->create();
        Comment::factory()->count(300)->create();
        SavedPost::factory()->count(400)->create();






    }
}
