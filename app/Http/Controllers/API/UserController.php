<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\User;
use App\Models\Image;
use App\Models\SavedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
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

    private function mapPostData($post, $currentDateTime)
    {
        $publishedAt = Carbon::parse($post->created_at);
        $dateDifference = $publishedAt->diffInDays($currentDateTime);

        return [
            'id' => $post->id,
            'title' => $post->name,
            'link' => $post->link,
            'description' => $post->description,
            'image' => $post->{'hero-image'},
            'user' => $post->user->only(['name', 'link', 'image']),
            // 'categories' => $post->categories->map(function ($category) {
            //     return $category->only(['name', 'link']);
            // }),
            // 'comments' => $post->comments->count(),
            'date' => $this->formatDate($publishedAt, $dateDifference),
        ];
    }

    public function getAuthenticatedUser()
    {
        return response()->json([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ]);
    }
    public function updateUserData(Request $request)
    {

        $user = User::find(auth()->user()->id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        return response()->json([
            'message' => 'Dane użytkownika zostały zaktualizowane pomyślnie.',
            'user' => $user
        ]);

    }

    public function getSavedPosts(Request $request)
    {
        // $perPage = $request->input('per_page', 3);
        $page = $request->input('page', 1);

        $currentDateTime = now();
        $currentUser = User::find(auth()->user()->id);

        $savedPosts = $currentUser->savedPosts()->select('user_id', 'post_id')->with('post', 'user')->paginate();
        $savedPostsMapped = $savedPosts->map(function ($savedPost) use ($currentDateTime) {
            return  $this->mapPostData($savedPost->post, $currentDateTime);
        });
        return response()->json([
            'posts' => $savedPostsMapped,
            $page,
            'pagination' => [
                "total" => $savedPosts->total(),
                'per_page' => $savedPosts->perPage(),
                'current_page' => $savedPosts->currentPage(),
                'last_page' => $savedPosts->lastPage(),
            ],
        ]);
    }

    public function getPostsUserLogged(Request $request)
    {
        $currentDateTime = now();

        $perPage = $request->input('per_page', 14);
        $page = $request->input('page', 1);
        $titleParam = $request->input('title');

        $postStatus = '';
        if ($titleParam === 'szkice') {
            $postStatus = 'draft';
        }
        if ($titleParam === 'null' || $titleParam === null) {
            $postStatus = 'published';
        }

        $currentUser = User::find(auth()->user()->id);

        $userPosts = $currentUser->posts()->where('status', '=', $postStatus)->with('user', 'categories')->paginate($perPage, ['*'], 'page', $page);

        $postsMapped = $userPosts->map(function ($userPosts) use ($currentDateTime) {
            return  $this->mapPostData($userPosts, $currentDateTime);
        });
        return response()->json([
            'posts' => $postsMapped,
            'pagination' => [
                "total" => $userPosts->total(),
                'per_page' => $userPosts->perPage(),
                'current_page' => $userPosts->currentPage(),
                'last_page' => $userPosts->lastPage(),
            ],
        ]);
    }

    //     // $validator = Validator::make($request->all(), [
    //     //     'image' => 'required|mimes:png,jpg'
    //     // ]);
    //     // if ($validator->fails()) {
    //     //     return response()->json([
    //     //         'status' => false,
    //     //         'message' => 'Please fix the errors',
    //     //         'errors' => $validator->errors()
    //     //     ]);
    //     // };
    //     // $img = $request->image;
    //     // $ext = $img->getClientOrininalExtension();
    //     // $imageName = time().'/'.$ext;
    //     //     $img ->move(public_path().'/storage', $imageName );
    //     // // $image = Image::create
    //     // $image = new Image;
    //     // $image -> name=$imageName;
    //     // $image->save();



    // }
}
