<?php

namespace App\Http\Controllers\API;


use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\SavedPost;
use App\Models\PostDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\SavedPostRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\CreatePostRequest;

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

    private function mapPostData($post, $currentDateTime, $savedPosts)
    {
        $publishedAt = Carbon::parse($post->created_at);
        $dateDifference = $publishedAt->diffInDays($currentDateTime);
        $savedPost = $savedPosts->where('post_id', $post->id)->first();

        return [
            'id' => $post->id,
            'title' => $post->name,
            'link' => $post->link,
            'description' => $post->description,
            'image' => $post->{'hero-image'},
            'saved' => $savedPost ? true : false,
            'user' => $post->user->only(['name', 'link', 'image']),
            'categories' => $post->categories->map(function ($category) {
                return $category->only(['name', 'link']);
            }),
            'comments' => $post->comments->count(),
            'date' => $this->formatDate($publishedAt, $dateDifference),
        ];
    }
    private function getPostsForHero()
    {
        return Post::with(['user', 'comments'])
            ->where('status', '=', 'published')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();
    }

    private function getRecommendedPosts()
    {
        return Post::with(['categories', 'user', 'comments'])
            ->where('status', '=', 'published')
            ->orderBy('created_at', 'asc')
            // ->take(3)
            ->take(10)
            ->get();
    }
    private function getNavPosts()
    {
        return Post::where('status', 'published') // Wybieramy tylko opublikowane posty
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->select('title', 'hero_image', 'link') // Wybieramy tylko kolumny 'title', 'hero_image' i 'link'
            ->get();
    }
    private function getAuthors()
    {
        return User::take(5)->select('name', 'link', 'image')->get()
            ->where('status', '=', 'published')
            ->map(function ($author) {
                $author['follow'] = false;
                return $author;
            });
    }

    private function getCategories()
    {
        return Category::take(5)->select('name', 'link')->get();
    }

    private function getSavedPosts()
    {
        $currentUser = auth()->user();
        return SavedPost::where('user_id', $currentUser->id)->select('user_id', 'post_id')->get();
    }

    private function mapPosts($posts, $currentDateTime, $savedPosts)
    {
        return $posts->map(function ($post) use ($currentDateTime, $savedPosts) {
            return $this->mapPostData($post, $currentDateTime, $savedPosts);
        });
    }

    public function getPostsListHome()
    {
        $currentDateTime = now();
        $postsListHero = $this->getPostsForHero();
        $postsListRecommended = $this->getRecommendedPosts();
        $authors = $this->getAuthors();
        $categories = $this->getCategories();
        $savedPosts = collect(); // Empty collection for not logged in users

        $postsListRecommendedMap = $this->mapPosts($postsListRecommended, $currentDateTime, $savedPosts);
        $postsListHeroMap = $this->mapPosts($postsListHero, $currentDateTime, $savedPosts);

        return response()->json([
            'savedPosts' => $savedPosts,
            // 'user' => auth()->user(),
            'hero' => $postsListHeroMap,
            'authors' => $authors,
            'categories' => $categories,
            'recommended' => $postsListRecommendedMap,
        ], 200);
    }

    public function getPostsListHomeLogged()
    {
        $currentDateTime = now();
        $postsListHero = $this->getPostsForHero();
        $postsListRecommended = $this->getRecommendedPosts();
        $authors = $this->getAuthors();
        $categories = $this->getCategories();
        // $currentUser = auth()->user();
        $savedPosts = $this->getSavedPosts();

        $postsListRecommendedMap = $this->mapPosts($postsListRecommended, $currentDateTime, $savedPosts);
        $postsListHeroMap = $this->mapPosts($postsListHero, $currentDateTime, $savedPosts);

        return response()->json([
            // 'savedPosts' => $savedPosts,
            // 'user' => $currentUser,
            'hero' => $postsListHeroMap,
            'authors' => $authors,
            'categories' => $categories,
            'recommended' => $postsListRecommendedMap,
        ], 200);
    }


    public function getPostsListUser(Request $request, $link)
    {
        $currentDateTime = now();
        $perPage = $request->input('per_page', 3);
        $page = $request->input('page', 1);
        $titleParam = $request->input('title');
        $order = $request->input('order', 'asc');
        $savedPosts = collect();

        if ($titleParam === 'popular') {
            $perPage = 2;
        }
        if ($titleParam === 'null' || $titleParam === null) {
            $order = 'desc';
        }

        $user = User::where('link', '=', $link)->first();

        $query = Post::where('user_id', '=', $user->id)->where('status', '=', 'published')->with(['postDetails', 'categories', 'user']);
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


            $currentPostsData = $this->mapPosts($userPosts, $currentDateTime, $savedPosts);
            return response()->json([
                'savedPosts' => $savedPosts,
                "user" => [
                    "name" => $user->name,
                    "image" => $user->image,
                    "postsCount" => $userPosts->total(),
                ],
                'posts' => $currentPostsData,
                "uniqueCategories" => $uniqueCategoriesData,
                'pagination' => [
                    'per_page' => $userPosts->perPage(),
                    'current_page' => $userPosts->currentPage(),
                    'last_page' => $userPosts->lastPage(),
                ],
            ], 200);
        } else {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }
    }


    public function getPostsListUserLogged(Request $request, $link)
    {
        $currentDateTime = now();
        $perPage = $request->input('per_page', 3);
        $page = $request->input('page', 1);
        $titleParam = $request->input('title');
        $order = $request->input('order', 'asc');
        $savedPosts = $this->getSavedPosts();

        if ($titleParam === 'popular') {
            $perPage = 2;
        }
        if ($titleParam === 'null' || $titleParam === null) {
            $order = 'desc';
        }

        $user = User::where('link', '=', $link)->first();
        $aboutUser = $user->detail()->first();
        $query = Post::where('user_id', '=', $user->id)->where('status', '=', 'published')->with(['postDetails', 'categories', 'user']);
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


            $currentPostsData = $this->mapPosts($userPosts, $currentDateTime, $savedPosts);
            return response()->json([
                "user" => [
                    "name" => $user->name,
                    "image" => $user->image,
                    "postsCount" => $userPosts->total(),
                    "about_user" => $aboutUser ? $aboutUser->about_user : false
                    // $aboutUser,
                ],
                'posts' => $currentPostsData,
                "uniqueCategories" => $uniqueCategoriesData,
                'pagination' => [
                    'per_page' => $userPosts->perPage(),
                    'current_page' => $userPosts->currentPage(),
                    'last_page' => $userPosts->lastPage(),
                ],
            ], 200);
        } else {
            return response()->json([
                'message' => 'Post not found.',
            ], 404);
        }
    }



    public function searchingNav(Request $request)
    {
        $searchQuery = $request->input('query');

        if ($searchQuery) {

            $queryPosts = Post::where('status', 'published')
                ->orderBy('created_at', 'asc')->where('name', 'like', '%' . $searchQuery . '%')->select('name', 'link', 'hero-image', 'description')->get();
            $queryAuthor = User::where('name', 'like', '%' . $searchQuery . '%')->select('name', 'link', 'image')->take(4)->get();
            $queryCategories = Category::where('name', 'like', '%' . $searchQuery . '%')->select('name', 'link')->take(4)->get();
            return response()->json([
                'results' => true,
                'posts' => $queryPosts,
                'authors' => $queryAuthor,
                'categories' => $queryCategories

            ]);
        }
        $popularAuthors  = User::select('image', 'name', 'link')->take(4)->get();
        $postsListRecommended = Post::where('status', 'published')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        $recommendedPostsData = $postsListRecommended->map(function ($post) {
            return [
                'title' => $post->name,
                'link' => $post->link,
                'image' => $post->{'hero-image'},
            ];
        });

        $categories = Category::select('link', 'name')->take(10)->get();
        return response()->json([
            'results' => false,
            'recommended' => $recommendedPostsData,
            'categories' => $categories,
            'authors' => $popularAuthors

        ]);
    }
    public function searchingNavLogged(Request $request)
    {
        $searchQuery = $request->input('query');

        $query = Post::where('status', 'published')
            ->orderBy('created_at', 'asc');
        if ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
            $results = $query->get();
            return response()->json([
                'results' => $results
            ]);
        }
        $popularAuthors  = User::select('image', 'name', 'link')->take(4)->get();
        $postsListRecommended = Post::where('status', 'published')
            ->orderBy('created_at', 'asc')
            ->take(5)
            ->get();

        $recommendedPostsData = $postsListRecommended->map(function ($post) {
            return [
                'title' => $post->name,
                'link' => $post->link,
                'image' => $post->{'hero-image'},
            ];
        });

        $categories = Category::select('link', 'name')->take(10)->get();
        return response()->json([
            'recommended' => $recommendedPostsData,
            'categories' => $categories,
            'authors' => $popularAuthors

        ]);
    }

    public function getPostByLink($link)
    {
        $currentDateTime = now();
        $currentPost = Post::with(['postDetails', 'categories', 'user', 'comments'])->where('link', '=', $link)->first();

        if (!$currentPost) {
            return response()->json([
                'message' => 'Post nie istnieje',
            ], 404);
        }

        $comments = $currentPost->comments()->with('user')->get();
        $publishedAt = Carbon::parse($currentPost->created_at);
        $dateDifference = $publishedAt->diffInDays($currentDateTime);
        $savedPosts = collect();

        $userPosts = Post::where('user_id', '=', $currentPost->user->id)
            ->where('id', '!=', $currentPost->id)
            ->where('status', '=', 'published')
            ->take(6)->get();

        $otherUserPostsMapping = $this->mapPosts($userPosts, $currentDateTime, $savedPosts);

        return response()->json([
            'id' => $currentPost->id,
            'title' => $currentPost->name,
            'date' => $this->formatDate($publishedAt, $dateDifference),
            'saved' => false,
            'commentsCount' => $currentPost->comments->count(),
            'description' => $currentPost->description,
            'image' => $currentPost->{'hero-image'},
            'user' => $currentPost->user->only(['name', 'link', 'image']),
            'postDetails' => $currentPost->postDetails->map(function ($detail) {
                return $detail->only(['text', 'class_name', 'image']);
            }),
            'categories' => $currentPost->categories->take(4)->map(function ($category) {
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
            'otherUserPosts' => $otherUserPostsMapping,
        ], 200);
    }

    public function getPostByLinkLogged($link)
    {
        $currentDateTime = now();
        $currentPost = Post::with(['postDetails', 'categories', 'user', 'comments'])->where('link', '=', $link)->first();

        if (!$currentPost) {
            return response()->json([
                'message' => 'Post nie istnieje',
            ], 404);
        }

        $comments = $currentPost->comments()->with('user')->orderBy('created_at', 'asc')->get();

        $publishedAt = Carbon::parse($currentPost->created_at);
        $dateDifference = $publishedAt->diffInDays($currentDateTime);
        $savedPosts = $this->getSavedPosts();

        $userPosts = Post::where('user_id', '=', $currentPost->user->id)
            ->where('id', '!=', $currentPost->id)
            ->where('status', '=', 'published')
            ->take(6)->get();

        $otherUserPostsMapping = $this->mapPosts($userPosts, $currentDateTime, $savedPosts);

        $currentUser = auth()->user();
        $saveCurrent = SavedPost::where('user_id', '=', $currentUser->id)
            ->where('post_id', '=', $currentPost->id)
            ->first(['user_id', 'post_id']);

        return response()->json([
            'id' => $currentPost->id,
            'title' => $currentPost->name,
            'date' => $this->formatDate($publishedAt, $dateDifference),
            'saved' => $saveCurrent ? true : false,
            'commentsCount' => $currentPost->comments->count(),
            'description' => $currentPost->description,
            'image' => $currentPost->{'hero-image'},
            'user' => $currentPost->user->only(['name', 'link', 'image']),
            'postDetails' => $currentPost->postDetails->map(function ($detail) {
                return $detail->only(['text', 'class_name', 'image']);
            }),
            'categories' => $currentPost->categories->take(4)->map(function ($category) {
                return $category->only(['name', 'link']);
            }),
            'comments' => $comments->map(function ($comment) {
                $publishedAtComment = Carbon::parse($comment->created_at);
                $dateDifferenceComment = $publishedAtComment->diffInDays(now());
                $user = auth()->user();

                return [
                    'id' => $comment->id,
                    'relaction' => $comment->relaction,
                    'text' => $comment->text,
                    'user' => $comment->user->only(['name', 'link', 'image']),
                    'date' => $this->formatDate($publishedAtComment, $dateDifferenceComment),
                    'toEdit' => $user->id == $comment->user->id ? true : false,
                ];
            }),
            'currentUser' =>  [
                'name' => auth()->user()->name,
                'link' => auth()->user()->link,
                'image' => auth()->user()->image,
            ],
            'otherUserPosts' => $otherUserPostsMapping,
        ], 200);
    }

    public function getUserProfilePage(Request $request)
    {
        $currentDateTime = now();
        $perPage = $request->input('per_page', 3);
        $page = $request->input('page', 1);
        $titleParam = $request->input('title');
        $order = $request->input('order', 'asc');
        $savedPosts = collect();

        if ($titleParam === 'popular') {
            $perPage = 2;
        }
        if ($titleParam === 'null' || $titleParam === null) {
            $order = 'desc';
        }

        $user = User::find(auth()->user()->id);
        $userDetails = $user->detail()->select('about_user')->first();
        $query = $user->posts()->where('status', '=', 'published')->paginate();
        $currentPostsData = $this->mapPosts($query, $currentDateTime, $savedPosts);

        return response()->json([
            'user' => [
                'name' => $user->name,
                'image' => $user->image,
                'postsCount' => $query->total()
            ],
            'about_user' => $userDetails->about_user,
            'posts' => $currentPostsData,
            'pagination' => [
                'per_page' => $query->perPage(),
                'current_page' => $query->currentPage(),
                'last_page' => $query->lastPage(),
            ],
        ]);

        // if ($userPosts) {

        //     $uniqueCategoriesData = $userPosts->flatMap(function ($post) {
        //         return $post->categories->map(function ($category) {
        //             return [
        //                 'name' => $category->name,
        //                 'link' => $category->link,
        //             ];
        //         });
        //     })->unique()->take(5)->values();
        //     $uniqueCategoriesData = $uniqueCategoriesData->unique();


        //     $currentPostsData = $this->mapPosts($userPosts, $currentDateTime, $savedPosts);
        //     return response()->json([
        //         "user" => [
        //             "name" => $user->name,
        //             "image" => $user->image,
        //             "postsCount" => $userPosts->total(),
        //         ],
        //         'posts' => $currentPostsData,
        //         "uniqueCategories" => $uniqueCategoriesData,
        //         'pagination' => [
        //             'per_page' => $userPosts->perPage(),
        //             'current_page' => $userPosts->currentPage(),
        //             'last_page' => $userPosts->lastPage(),
        //         ],
        //     ], 200);
        // } else {
        //     return response()->json([
        //         'message' => 'Post not found.',
        //     ], 404);
        // }
    }

    public function postSaved(SavedPostRequest $request)
    {
        $user = $request->get('user_id');
        $post = $request->get('post_id');

        // $request->validated();
        $formData = $request->all();
        $findRecord = SavedPost::where('post_id', $post)->where('user_id', $user)->first();

        if (!$findRecord) {
            SavedPost::create(
                $formData
            );
            return response()->json(['message' => 'Zapisano post'], 200);
        } else {
            return response()->json(['message' => 'Post jest już zapisany'], 422);
        }
    }

    public function postUnSaved(SavedPostRequest $request)
    {

        $formData = $request->all();
        $user = $formData['user_id'];
        $post = $formData['post_id'];

        $findRecord = SavedPost::where('post_id', $post)->where('user_id', $user)->first();

        if ($findRecord) {
            SavedPost::where('post_id', $post)->where('user_id', $user)->delete();
            return response()->json(['message' => 'Usunięto zapisany post'], 200);
        } else {
            return response()->json(['message' => 'Nie znaleziono zapisu dla danego postu'], 422);
        }
    }

    public function createPost(CreatePostRequest $request)
    {
        $categoryIds = json_decode($request->get('category_ids'), true); // Pobieranie ID kategorii z formularza oraz rzekształcamy string "[1,2,3]" na tablicę [1, 2, 3]

        $formData = $request->all();
        $imageName = $request->file('hero-image')->hashName();
        $path = Storage::putFileAs('photos-hero', $request->file('hero-image'), $imageName);

        $formData['hero-image'] = asset("storage/$path");
        $createdPost = Post::create($formData);

        foreach ($categoryIds as $categoryId) {
            $createdPost->categories()->attach($categoryId);
        }

        return response()->json(['message' => 'Post successfully created'], 200);
    }
}
