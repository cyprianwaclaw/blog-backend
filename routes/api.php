<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\checkUserLogin;
use Database\Factories\userDetailFactory;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\UserDetailController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/register', [AuthController::class, 'registerUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

// forgotPassword
//!aby działało auth() lub $request->user() musi byc w middlewarze
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('logged')->group(function () {
        Route::get('/data/home', [PostController::class, 'getPostsListHomeLogged']);
        Route::get('/data/category/{link}', [CategoryController::class, 'getPostsListCategoryLogged']);
        Route::get('/data/user/{link}', [PostController::class, 'getPostsListUserLogged']);

        Route::get('/user/profile', [PostController::class, 'getUserProfilePage']);
        Route::get('/user/saved-posts', [UserController::class, 'getSavedPosts']);
        Route::get('/user/posts', [UserController::class, 'getPostsUserLogged']);

        Route::post('/user/details', [UserDetailController::class, 'updateAbout']);

        Route::get('/post/{link}', [PostController::class, 'getPostByLinkLogged']);
        Route::post('/save', [PostController::class, 'postSaved']);
        Route::post('/unsave', [PostController::class, 'postUnSaved']);
        Route::post('/create/post', [PostController::class, 'createPost']);
        Route::post('/search-nav', [PostController::class, 'searchingNav']);
        // Route::post('/search-nav', [PostController::class, 'searchingNavLogged']);

        Route::get('/user', [UserController::class, 'getAuthenticatedUser']);
        Route::post('/user/change', [UserController::class, 'updateUserData']);
        // searchingNav()
        Route::post('/change-password',  [AuthController::class, 'updatePassword']);


        // createNewCommen
        // deleteComment
        Route::post( '/new-comment', [CommentController::class, 'createNewComment'] );
        Route::post('/delete-comment', [CommentController::class, 'deleteComment']);
        Route::post('/update-comment', [CommentController::class, 'updateComment']);

    });
});

Route::get('/data/home', [PostController::class, 'getPostsListHome']);
Route::get('/data/category/{link}', [CategoryController::class, 'getPostsListCategory']);
Route::get('/data/user/{link}', [PostController::class, 'getPostsListUser']);
Route::get('/post/{link}', [PostController::class, 'getPostByLink']);
Route::post('/search-nav', [PostController::class, 'searchingNav']);