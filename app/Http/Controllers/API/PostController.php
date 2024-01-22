<?php

namespace App\Http\Controllers\API;


use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\PostDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PostStoreRequest;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
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

    public function getPostsListHome()
    {
        $currentDateTime = now();
        $postsListHero = Post::with(['user', 'comments'])
            ->orderBy('created_at', 'asc')
            ->take(3)->get();
        $postsListrecommended = Post::with(['categories', 'user', 'comments'])
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        $authors = User::take(5)->select('name', 'link', 'image')->get();
        $authors = $authors->map(function ($author) {
            $author['follow'] = false;
            return $author;
        });

        $categories = Category::take(5)->select('name', 'link')->get();

        $postsListrecommendedMap = $postsListrecommended->map(function ($post) use ($currentDateTime) {
            $publishedAt = Carbon::parse($post->created_at);
            $dateDifference = $publishedAt->diffInDays($currentDateTime);
            return [
                'title' => $post->name,
                'link' => $post->link,
                'description' => $post->description,
                'image' => $post->{'hero-image'},
                'saved' => false,
                'user' => $post->user->only(['name', 'link', 'image']),
                'categories' => $post->categories->map(function ($category) {
                    return $category->only(['name', 'link']);
                }),
                'comments' => $post->comments->count(),
                'date' => $this->formatDate($publishedAt, $dateDifference),
            ];
        });

        $postsListHeroMap = $postsListHero->map(function ($post) use ($currentDateTime) {
            $publishedAt = Carbon::parse($post->created_at);
            $dateDifference = $publishedAt->diffInDays($currentDateTime);
            return [
                'title' => $post->name,
                'link' => $post->link,
                'description' => $post->description,
                'image' => $post->{'hero-image'},
                'saved' => false,
                'user' => $post->user->only(['name', 'link', 'image']),
                'comments' => $post->comments->count(),
                'date' => $this->formatDate($publishedAt, $dateDifference),
            ];
        });

        return response()->json(
            [
                'hero' => $postsListHeroMap,
                'authors' => $authors,
                'categories' => $categories,
                'recommended' => $postsListrecommendedMap,
            ],
            200
        );
    }



    public function getPostsListHomeLogged()
    {
        $currentDateTime = now();
        $postsListHero = Post::with(['user', 'comments'])
            ->orderBy('created_at', 'asc')
            ->take(3)->get();
        $postsListrecommended = Post::with(['categories', 'user', 'comments'])
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        $authors = User::take(5)->select('name', 'link', 'image')->get();
        $authors = $authors->map(function ($author) {
            $author['follow'] = false;
            return $author;
        });

        $categories = Category::take(5)->select('name', 'link')->get();

        $postsListrecommendedMap = $postsListrecommended->map(function ($post) use ($currentDateTime) {
            $publishedAt = Carbon::parse($post->created_at);
            $dateDifference = $publishedAt->diffInDays($currentDateTime);
            return [
                'title' => $post->name,
                'link' => $post->link,
                'description' => $post->description,
                'image' => $post->{'hero-image'},
                'saved' => false,
                'user' => $post->user->only(['name', 'link', 'image']),
                'categories' => $post->categories->map(function ($category) {
                    return $category->only(['name', 'link']);
                }),
                'comments' => $post->comments->count(),
                'date' => $this->formatDate($publishedAt, $dateDifference),
            ];
        });

        $postsListHeroMap = $postsListHero->map(function ($post) use ($currentDateTime) {
            $publishedAt = Carbon::parse($post->created_at);
            $dateDifference = $publishedAt->diffInDays($currentDateTime);
            return [
                'title' => $post->name,
                'link' => $post->link,
                'description' => $post->description,
                'image' => $post->{'hero-image'},
                'saved' => true,
                'user' => $post->user->only(['name', 'link', 'image']),
                'comments' => $post->comments->count(),
                'date' => $this->formatDate($publishedAt, $dateDifference),
            ];
        });

        return response()->json(
            [
                'hero' => $postsListHeroMap,
                'authors' => $authors,
                'categories' => $categories,
                'recommended' => $postsListrecommendedMap,
            ],
            200
        );
    }


    public function getPostsListUser(Request $request, $link)
    {

        //     $currentDateTime = now();
        //     $perPage = $request->input('per_page', 3);
        //     $page = $request->input('page', 1);
        //     $titleParam = $request->input('title');
        //     $order = $request->input('order', 'asc');

        //     // Dodaj warunek sprawdzający wartość 'title' i ustaw per_page na 2
        //     // if ($titleParam === 'latest') {
        //     //     $perPage = 2;
        //     // }
        //     if ($titleParam === 'popular') {
        //         $perPage = 2;
        //     }
        //     if ($titleParam === 'null' || $titleParam === null) {
        //         $order = 'desc';
        //     }

        //     $category = Category::where('link', $link)->first();
        //     $posts = $category->posts()->with('comments', 'user', 'categories');
        //     $posts->orderBy('created_at', $order);

        //     $paginatedPosts = $posts->paginate($perPage, ['*'], 'page', $page);
        //     $usersInCategory = $category->posts()->take(5)->with('user')->get()->pluck('user')->unique();
        //     $userData = $usersInCategory->map(function ($user) {
        //         return [
        //             'id' => $user->id,
        //             'name' => $user->name,
        //             'image' => $user->image,
        //             'link' => $user->link,
        //         ];
        //     })->values();

        //     $currentPostsData = $paginatedPosts->map(function ($post) use ($currentDateTime) {
        //         $publishedAt = Carbon::parse($post->created_at);
        //         $dateDifference = $publishedAt->diffInDays($currentDateTime);
        //         return [
        //             'title' => $post->name,
        //             'link' => $post->link,
        //             'description' => $post->description,
        //             'image' => $post->{'hero-image'},
        //             'user' => $post->user->only(['name', 'link', 'image']),
        //             'saved' => false,
        //             'categories' => $post->categories->map(function ($category) {
        //                 return $category->only(['name', 'link']);
        //             }),
        //             'comments' => $post->comments->count(),
        //             'date' => $this->formatDate($publishedAt, $dateDifference),
        //         ];
        //     });

        //     $uniqueCategoriesData = $paginatedPosts->flatMap(function ($post) {
        //         return $post->categories->map(function ($category) {
        //             return [
        //                 'name' => $category->name,
        //                 'link' => $category->link,
        //             ];
        //         });
        //     })->unique()->take(5)->values();
        //     $uniqueCategoriesData = $uniqueCategoriesData->unique();

        //     return response()->json([
        //         "category" => [
        //             "name" => $category->name,
        //             "link" => $category->link,
        //             "postsCount" => $paginatedPosts->total(), // użyj total() dla całkowitej liczby postów
        //         ],
        //         "usersInCategory" => $userData,
        //         "uniqueCategories" => $uniqueCategoriesData,
        //         "posts" => $currentPostsData,
        //         'pagination' => [
        //             'per_page' => $paginatedPosts->perPage(),
        //             'current_page' => $paginatedPosts->currentPage(),
        //             'last_page' => $paginatedPosts->lastPage(),
        //         ],
        //     ]);
        // }
        $currentDateTime = now();
        $perPage = $request->input('per_page', 3);
        $page = $request->input('page', 1);
        $titleParam = $request->input('title');
        $order = $request->input('order', 'asc');

        if ($titleParam === 'popular') {
            $perPage = 2;
        }
        if ($titleParam === 'null' || $titleParam === null) {
            $order = 'desc';
        }

        $user = User::where('link', '=', $link)->first();

        $query = Post::where('user_id', '=', $user->id)->with(['postDetails', 'categories', 'user']);


        $userPosts = $query->paginate($perPage, ['*'], 'page', $page);

        if ($userPosts) {

            $uniqueCategoriesData = $userPosts->flatMap(function ($post) {
                return $post->categories->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'link' => $category->link,
                    ];
                });
            })->unique()->take(5)->values();
            $uniqueCategoriesData = $uniqueCategoriesData->unique();


            $currentPostsData = $userPosts->map(function ($post) use ($currentDateTime) {
                $publishedAt = Carbon::parse($post->created_at);
                $dateDifference = $publishedAt->diffInDays($currentDateTime);
                return [
                    'title' => $post->name,
                    'user_id' => $post->user_id,
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

            return response()->json(
                [
                    "user" => [
                        "link" => $user->link,
                        "name" => $user->name,
                        "image" => $user->image,
                        "postsCount" => $userPosts->total(), // użyj total() dla całkowitej liczby postów
                    ],
                    'posts' => $currentPostsData,
                    "uniqueCategories" => $uniqueCategoriesData,
                    'pagination' => [
                        // 'total' => $userPosts->total(),
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


    public function getPostByLink($link)
    {
        $currentPost = Post::with(['postDetails', 'categories', 'user', 'comments'])->where('link', '=', $link)->first();

        if ($currentPost) {
            $comments = $currentPost->comments()->with('user')->get();

            return response()->json(
                [
                    'title' => $currentPost->name,
                    'link' => $currentPost->link,
                    'description' => $currentPost->description,
                    'image' => $currentPost->{'hero-image'},
                    'user' => $currentPost->user->only(['name', 'link']),
                    'postDetails' => $currentPost->postDetails->map(function ($detail) {
                        return $detail->only(['text', 'class_name', 'image']);
                    }),
                    'categories' => $currentPost->categories->map(function ($category) {
                        return $category->only(['name', 'link']);
                    }),
                    'comments' => $comments->map(function ($comment) {
                        return [
                            'title' => $comment->title,
                            'relaction' => $comment->relaction,
                            'text' => $comment->text,
                            'user' => $comment->user->only(['name', 'link', 'image']),
                            'created_at' => $comment->created_at,
                        ];
                    }),
                ],
                200
            );
        } else {
            return response()->json([
                'message' => 'Post nie istnieje',
            ], 404);
        }
    }

    // TODO:uzepełnic
    // public function store(PostStoreRequest $request)
    // {
    //     $name = $request->file('image')->hashName();
    //     $path = Storage::putFileAs('photos', $request->file('image'), $name);
    //     $currentUserId = auth()->user()->id;
    //     $formData = $request->validated();
    //     $formData['image'] = asset("storage/$path");
    //     $formData['user_id'] = $currentUserId;
    //     $createdPost = Post::create($formData);
    //     $createdPost->categories()->attach($request->input('category_ids'));
    //     // przy tworzeniu nowych, gdy chcmy pobrac relacje trzeba ja "zaladowac"
    //     // Log::info("Path to saved image: $path");
    //     $withUser = $createdPost->load(['user']);
    //     return response()->json([
    //         'message' => 'Post successfully created.',
    //         'created_post' =>  $withUser,
    //     ], 200);
    // }
}
