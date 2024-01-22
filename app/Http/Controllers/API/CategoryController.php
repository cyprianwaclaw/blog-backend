<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{

    /**
     * Formatuje datę publikacji posta na podstawie różnicy w dniach.
     *
     * @param  \Carbon\Carbon  $publishedAt
     * @param  int  $dateDifference
     * @return string
     */
    private function formatDate($publishedAt, $dateDifference)
    {
        if ($dateDifference === 0) {
            // Jeśli post opublikowany dzisiaj, wyświetl "dziś o {godzina}"
            return 'dziś o ' . $publishedAt->format('H:i');
        } elseif ($dateDifference === 1) {
            // Jeśli post opublikowany wczoraj, wyświetl "wczoraj o {godzina}"
            return 'wczoraj o ' . $publishedAt->format('H:i');
        } elseif ($dateDifference <= 14) {
            // Jeśli post opublikowany w ciągu ostatniego tygodnia, wyświetl "X dni temu"
            return $dateDifference . ' dni temu';
        } else {
            // W przeciwnym razie, wyświetl pełną datę
            return $publishedAt->format('d.m.Y');
        }
    }


    public function getPostsListCategory(Request $request, $link)
    {
        $currentDateTime = now();
        $perPage = $request->input('per_page', 3);
        $page = $request->input('page', 1);
        $titleParam = $request->input('title');
        $order = $request->input('order', 'asc');

        // Dodaj warunek sprawdzający wartość 'title' i ustaw per_page na 2
        // if ($titleParam === 'latest') {
        //     $perPage = 2;
        // }
        if ($titleParam === 'popular') {
            $perPage = 2;
        }
        if ($titleParam === 'null' || $titleParam === null) {
            $order = 'desc';
        }

        $category = Category::where('link', $link)->first();
        $posts = $category->posts()->with('comments', 'user', 'categories');
        $posts->orderBy('created_at', $order);

        $paginatedPosts = $posts->paginate($perPage, ['*'], 'page', $page);
        $usersInCategory = $category->posts()->take(5)->with('user')->get()->pluck('user')->unique();
        $userData = $usersInCategory->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'image' => $user->image,
                'link' => $user->link,
            ];
        })->values();

        $currentPostsData = $paginatedPosts->map(function ($post) use ($currentDateTime) {
            $publishedAt = Carbon::parse($post->created_at);
            $dateDifference = $publishedAt->diffInDays($currentDateTime);
            return [
                'title' => $post->name,
                'link' => $post->link,
                'description' => $post->description,
                'image' => $post->{'hero-image'},
                'user' => $post->user->only(['name', 'link', 'image']),
                'saved' => false,
                'categories' => $post->categories->map(function ($category) {
                    return $category->only(['name', 'link']);
                }),
                'comments' => $post->comments->count(),
                'date' => $this->formatDate($publishedAt, $dateDifference),
            ];
        });

        $uniqueCategoriesData = $paginatedPosts->flatMap(function ($post) {
            return $post->categories->map(function ($category) {
                return [
                    'name' => $category->name,
                    'link' => $category->link,
                ];
            });
        })->unique()->take(5)->values();
        $uniqueCategoriesData = $uniqueCategoriesData->unique();

        return response()->json([
            "category" => [
                "name" => $category->name,
                "link" => $category->link,
                "postsCount" => $paginatedPosts->total(), // użyj total() dla całkowitej liczby postów
            ],
            "usersInCategory" => $userData,
            "uniqueCategories" => $uniqueCategoriesData,
            "posts" => $currentPostsData,
            'pagination' => [
                'per_page' => $paginatedPosts->perPage(),
                'current_page' => $paginatedPosts->currentPage(),
                'last_page' => $paginatedPosts->lastPage(),
            ],
        ]);
    }

    public function getPostsListCategoryLogged(Request $request, $link)
    {
        $currentDateTime = now();
        $perPage = $request->input('per_page', 3);
        $page = $request->input('page', 1);
        $titleParam = $request->input('title');
        $order = $request->input('order', 'asc');

        // Dodaj warunek sprawdzający wartość 'title' i ustaw per_page na 2
        // if ($titleParam === 'latest') {
        //     $perPage = 2;
        // }
        if ($titleParam === 'popular') {
            $perPage = 2;
        }
        if ($titleParam === 'null' || $titleParam === null) {
            $order = 'desc';
        }

        $category = Category::where('link', $link)->first();
        $posts = $category->posts()->with('comments', 'user', 'categories');
        $posts->orderBy('created_at', $order);

        $paginatedPosts = $posts->paginate($perPage, ['*'], 'page', $page);
        $usersInCategory = $category->posts()->take(5)->with('user')->get()->pluck('user')->unique();
        $userData = $usersInCategory->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'image' => $user->image,
                'link' => $user->link,
            ];
        })->values();

        $currentPostsData = $paginatedPosts->map(function ($post) use ($currentDateTime) {
            $publishedAt = Carbon::parse($post->created_at);
            $dateDifference = $publishedAt->diffInDays($currentDateTime);
            return [
                'title' => $post->name,
                'link' => $post->link,
                'description' => $post->description,
                'image' => $post->{'hero-image'},
                'user' => $post->user->only(['name', 'link', 'image']),
                'saved' => false,
                'categories' => $post->categories->map(function ($category) {
                    return $category->only(['name', 'link']);
                }),
                'comments' => $post->comments->count(),
                'date' => $this->formatDate($publishedAt, $dateDifference),
            ];
        });

        $uniqueCategoriesData = $paginatedPosts->flatMap(function ($post) {
            return $post->categories->map(function ($category) {
                return [
                    'name' => $category->name,
                    'link' => $category->link,
                ];
            });
        })->unique()->take(5)->values();
        $uniqueCategoriesData = $uniqueCategoriesData->unique();

        return response()->json([
            "category" => [
                "name" => $category->name,
                "link" => $category->link,
                "postsCount" => $paginatedPosts->total(), // użyj total() dla całkowitej liczby postów
            ],
            "usersInCategory" => $userData,
            "uniqueCategories" => $uniqueCategoriesData,
            "posts" => $currentPostsData,
            'pagination' => [
                'per_page' => $paginatedPosts->perPage(),
                'current_page' => $paginatedPosts->currentPage(),
                'last_page' => $paginatedPosts->lastPage(),
            ],
        ]);
    }
}

