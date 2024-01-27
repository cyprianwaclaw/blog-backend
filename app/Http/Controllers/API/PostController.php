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
            'savedPost' => $savedPost,
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
            ->orderBy('created_at', 'asc')
            ->take(3)
            ->get();
    }

    private function getRecommendedPosts()
    {
        return Post::with(['categories', 'user', 'comments'])
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();
    }

    private function getAuthors()
    {
        return User::take(5)->select('name', 'link', 'image')->get()
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


            $currentPostsData = $this->mapPosts($userPosts, $currentDateTime, $savedPosts);
            return response()->json([
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
            ->take(6)->get();

        $otherUserPostsMapping = $this->mapPosts($userPosts, $currentDateTime, $savedPosts);

        return response()->json([
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

        $comments = $currentPost->comments()->with('user')->get();
        $publishedAt = Carbon::parse($currentPost->created_at);
        $dateDifference = $publishedAt->diffInDays($currentDateTime);
        $savedPosts = $this->getSavedPosts();

        $userPosts = Post::where('user_id', '=', $currentPost->user->id)
            ->where('id', '!=', $currentPost->id)
            ->take(6)->get();

        $otherUserPostsMapping = $this->mapPosts($userPosts, $currentDateTime, $savedPosts);

        $currentUser = auth()->user();
        $saveCurrent = SavedPost::where('user_id', '=', $currentUser->id)
            ->where('post_id', '=', $currentPost->id)
            ->first(['user_id', 'post_id']);

        return response()->json([
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
        $user = $request->get('user_id');
        $post = $request->get('post_id');

        $findRecord = SavedPost::where('post_id', $post)->where('user_id', $user)->first();

        if ($findRecord) {
            SavedPost::where('post_id', $post)->where('user_id', $user)->delete();
            return response()->json(['message' => 'Usunięto zapisane posty'], 200);
        } else {
            return response()->json(['message' => 'Nie znaleziono zapisu dla danego posta'], 422);
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
