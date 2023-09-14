<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostsController;
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

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    /** Get userInfo by ID */
    Route::get('/user/{id}', [UserController::class, 'show']);
    /** Edit userInfo */
    Route::patch('/user', [UserController::class, 'update']);

    
    /**
     * User Logout
     */
    Route::post('/logout', [LogoutController::class, 'logout']);
});
    /** Delete Userdata in DB */
    Route::delete('/user/{id}', [UserController::class, 'destroy']);

/**
 * User Login
 */
Route::post('/login', [LoginController::class, 'authenticate']);

/**
 * User Register
 */
Route::post('/register', [UserController::class, 'store']);



/* 게시글 작성 post 요청 받을 시 */
Route::post('/createPost', [PostsController::class, 'createPost']);

/* 게시글 삭제 요청*/
Route::delete('/posts/{id}', [PostsController::class, 'deletePost']);

/* 게시물 조회 요청 */
// 특정 id로 조회 요청
Route::get('/posts/{id}', [PostsController::class, 'retrievePostId']);
// 특정 태그 게시물 조회
Route::get('/postTags', [PostsController::class, 'retrievePostTagId']);
// 조회수 순 조회
Route::get('/postSortingView', [PostsController::class, 'retrievePostView']);
// 최근 게시물 순 조회
Route::get('/postSortingRecent', [PostsController::class, 'retrieveRecentPost']);

/* 게시물 수정 요청 */
Route::patch('/posts/update/{id}', [PostsController::class, 'updatePost']);