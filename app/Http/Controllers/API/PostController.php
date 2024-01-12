<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\PostDetails;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function getUserPosts(Request $request, $id)
    {

        $perPage = $request->input('per_page', 14);
        $page = $request->input('page', 1);
        $order = $request->input('order', 'asc');

        $query = Post::where('user_id', '=', $id)->with(['postDetails', 'categories', 'user']);

        // Jeśli nie określono żadnego porządku, dodaj losowy porządek
        if (!$order) {
            $query->inRandomOrder();
        } else {
            $query->orderBy('created_at', $order);
        }

        $userPosts = $query->paginate($perPage, ['*'], 'page', $page);

        // $userPosts = Post::where('user_id', '=', $id)->with(['postDetails', 'categories', 'user'])->orderBy('created_at', $order)->paginate($perPage, ['*'], 'page', $page);
        if ($userPosts) {
            $currentPostsData = $userPosts->map(function ($post) {
                return [
                    'title' => $post->name,
                    'description' => $post->description,
                    'image' => $post->{'hero-image'},
                    'user' => $post->user->only(['name', 'link']),
                    'postDetails' => $post->postDetails->map(function ($detail) {
                        return $detail->only(['text', 'class_name', 'image']);
                    }),
                    'categories' => $post->categories->map(function ($category) {
                        return $category->only(['name', 'link']);
                    }),
                ];
            });
            return response()->json(
                [
                    'data' => $currentPostsData,
                    'pagination' => [
                        'total' => $userPosts->total(),
                        'per_page' => $userPosts->perPage(),
                        'current_page' => $userPosts->currentPage(),
                        'last_page' => $userPosts->lastPage(),
                    ],

                ],
                200
            );
        } else {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }
    }

    public function show($id)
    {
        $currentPost = Post::with(['postDetails', 'categories', 'user'])->find($id);

        if ($currentPost) {
            return response()->json(
                [
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
                ],
                200
            );
        } else {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }
    }

    // TODO:uzepełnic
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
