<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
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
// Route::post('/upload', [UserController::class, 'upload']);

Route::get('/post/{id}', [PostController::class, 'show']);
Route::get('/posts/user/{id}', [PostController::class, 'getUserPosts']);

Route::get('/category/{id}/posts', [CategoryController::class, 'getCategoryWithPosts']);
Route::get('/category/{id}', [CategoryController::class, 'getSingleCategory']);

// aby działało auth() musi byc w middlewarze
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);

});
