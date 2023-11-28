<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConvoController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::group(['middleware'=>['auth:sanctum']], function(){
    Route::get('/user-data', [UserController::class, 'getUserData']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/user/{id}', [UserController::class, 'deleteUser']);
    Route::get('/profile/{id}', [UserController::class, 'showOne']);
    Route::post('/post', [PostController::class, 'store']);
    Route::get('/post', [PostController::class, 'showAll']);
    Route::delete('/post/{id}', [PostController::class, 'deletePost']);
    Route::delete('/repost/{id}', [PostController::class, 'deleteRepost']);
    Route::post('/post/{id}/comment', [CommentController::class, 'store']);
    Route::delete('/comment/{id}', [CommentController::class, 'delete']);
    Route::post('/post/{id}/like', [PostController::class, 'addLike']);
    Route::delete('/post/{id}/like', [PostController::class, 'removeLike']);
    Route::post('/post/{id}/repost', [PostController::class, 'addRepost']);
    Route::get('/post/user/{id}',[PostController::class, 'getUsersPosts']);
    Route::get('/repost/user/{id}',[PostController::class, 'getUsersReposts']);
    Route::get('/repost', [PostController::class, 'getAllReposts']);
    Route::post('/user/{id}/follow', [UserController::class, 'addFollow']);
    Route::delete('/user/{id}/follow', [UserController::class, 'removeFollow']);
    Route::get('/follow/{id}',[PostController::class, 'getFollowedPosts']);
    Route::get('/search', [UserController::class, 'searchUser']);
    Route::post('/edit-profile/{id}', [UserController::class, 'editProfile']);
    Route::get('/file', [PostController::class, 'getFile']);
    Route::get('/{id}/convo',[ConvoController::class, 'getUserConvos']);
    Route::post('/convo/new',[ConvoController::class, 'startNewConvo']);
    Route::post('/convo/{id}/message/new', [ConvoController::class, 'postNewMessage']);
    Route::get('/users',[UserController::class, 'getAllUsers']);
    Route::delete('/convo/{id}', [ConvoController::class, 'deleteConvo']);
    Route::get('/search/mention', [UserController::class, 'findUserForMention']);
    Route::get('/get/mention', [UserController::class, 'getMentionedUsers']);
});