<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Models\PostDetails;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function show($id)
    {
        try {
            $currentPost = Post::with(['postDetails', 'categories', 'user'])->findOrFail($id);
            return response()->json([
                // 'id' => $currentPost->id,
                'title' => $currentPost->name,
                'description' => $currentPost->description,
                'image' => $currentPost->{'hero-image'},
                'user' => $currentPost->user->only(['name', 'link']),
                'postDetails' => $currentPost->postDetails->map(function ($detail) {
                    return $detail->only(['text', 'class_name', 'image']);
                }),
                'categories' => $currentPost->categories->map(function ($category) {
                    return $category->only(['name', 'link']);
                }),
            ], 200);

            return response()->json([
                $currentPost,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }
    }


    public function store(PostStoreRequest $request)
    {
        $name = $request->file('image')->hashName();
        $path = Storage::putFileAs('photos', $request->file('image'), $name);
        $currentUserId = auth()->user()->id;
        $formData = $request->validated();
        $formData['image'] = asset("storage/$path");
        $formData['user_id'] = $currentUserId;
        $createdPost = Post::create($formData);
        $createdPost->categories()->attach($request->input('category_ids'));
        // przy tworzeniu nowych, gdy chcmy pobrac relacje trzeba ja "zaladowac"
        // Log::info("Path to saved image: $path");
        $withUser = $createdPost->load(['user']);
        return response()->json([
            'message' => 'Post successfully created.',
            'created_post' =>  $withUser,
        ], 200);
    }
}
