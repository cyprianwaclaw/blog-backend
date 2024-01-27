<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\checkUserLogin;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CategoryController;
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


//!aby działało auth() lub $request->user() musi byc w middlewarze
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('logged')->group(function () {
        Route::get('/data/home', [PostController::class, 'getPostsListHomeLogged']);
        Route::get('/data/category/{link}', [CategoryController::class, 'getPostsListCategoryLogged']);
        Route::get('/data/user/{link}', [PostController::class, 'getPostsListUserLogged']);
        Route::get('/post/{link}', [PostController::class, 'getPostByLinkLogged']);
        Route::post('/save', [PostController::class, 'postSaved']);
        Route::post('/unsave', [PostController::class, 'postUnSaved']);
        Route::post('/create/post', [PostController::class, 'createPost']);
        // Route::post('/test', [PostController::class, 'test']);

        // }}/logged/posts/user
        // /logged/save/10
        Route::get('/user', [UserController::class, 'getAuthenticatedUser']);
    });
});

Route::get('/data/home', [PostController::class, 'getPostsListHome']);
Route::get('/data/category/{link}', [CategoryController::class, 'getPostsListCategory']);
Route::get('/data/user/{link}', [PostController::class, 'getPostsListUser']);
Route::get('/post/{link}', [PostController::class, 'getPostByLink']);
