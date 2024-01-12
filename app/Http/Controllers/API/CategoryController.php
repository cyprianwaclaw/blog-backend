<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class CategoryController extends Controller
{

    public function getSingleCategory($categoryId)
    {

        $category = Category::findOrFail($categoryId);

        return response()->json(
            [
                'category' => $category
            ],
            200
        );
    }

    public function getCategoryWithPosts(Request $request, $categoryId)
    {

        $perPage = $request->input('per_page', 14);
        $page = $request->input('page', 1);
        $order = $request->input('order', 'asc');

        // Pobierz informacje o kategorii
        $category = Category::findOrFail($categoryId);

        // Pobierz posty dla danej kategorii
        $posts = $category->posts() ->orderBy('created_at', $order)
            ->with(['categories', 'user'])
            ->paginate($perPage, ['*'], 'page', $page);

        $postsData = $posts->map(function ($post) {
            return [
                'title' => $post->name,
                'description' => $post->description,
                'image' => $post->{'hero-image'},
                'user' => $post->user->only(['name', 'link']),
                'categories' => $post->categories->map(function ($category) {
                    return $category->only(['name', 'link']);
                }),
            ];
        });

        return response()->json(
            [
                'category' => $category->only(['name', 'link']),
                'posts' => [
                    'data' => $postsData,
                    'pagination' => [
                        'total' => $posts->total(),
                        'per_page' => $posts->perPage(),
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                    ],
                ],
            ],
            200
        );
    }
}
