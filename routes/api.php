<?php

use App\Helpers\ImageHelper;
use App\Http\Controllers\SignoutController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
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

// Route::middleware('auth:sanctum')->group(function() {
    Route::resource('user', UserController::class)->only([
        'index', 'show', 'destroy'
    ]);
    Route::patch('/user', [UserController::class, 'update']);
    /** User Logout */
    Route::post('/logout', LogoutController::class);

// });
/** User Login */
Route::post('/login', LoginController::class);
/** User Register */
Route::post('/register', RegisterController::class);


/* 게시글 작성 post 요청 받을 시 */
Route::post('/createPost', [PostController::class, 'createPost']);

/* 게시글 삭제 요청*/
Route::delete('/posts/{id}', [PostController::class, 'deletePost']);

/* 게시물 조회 요청 */
// 특정 id로 조회 요청
Route::get('/posts/{id}', [PostController::class, 'retrievePostId']);
// 특정 태그 게시물 조회
Route::get('/postTags', [PostController::class, 'retrievePostTagId']);
// 조회수 순 조회
Route::get('/postSortingView', [PostController::class, 'retrievePostView']);
// 최근 게시물 순 조회
Route::get('/postSortingRecent', [PostController::class, 'retrieveRecentPost']);

/* 게시물 수정 요청 */
Route::patch('/posts/update/{id}', [PostController::class, 'updatePost']);

/* 게시글 검색 요청 */
// 게시글 연관 제목+내용 검색
Route::get('/posts/search/{titleArticle}',[PostController::class, 'search']); 

// 게시글 제목+내용 일치 검색
Route::get('/posts/search/{titleArticle}/correct',[PostController::class, 'searchCorrect']);

// 게시글 연관 태그 검색
Route::get('/posts/search/tag/{tag}',[PostController::class, 'relatedPostTags']); 

// 유저 게시글 가져오기
Route::get('/posts/users/{userId}',[PostController::class, 'userPosts']);

// 게시글 이미지 AWS(S3) 저장 및 path 반환
Route::post('/posts/storeImage',[PostController::class,'createImage']);

// 이미지 삭제
Route::post('/posts/deleteImage',[PostController::class,'deleteImage']);

