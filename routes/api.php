<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
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



#Auth EndPoints
Route::post('/signup', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'register']);
Route::post('/token', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'login']);

#Protected EndPoints
Route::middleware(['auth:sanctum', 'lastOnlineTime'])->group(function () {

    Broadcast::routes();

    Route::get('/status',[\App\Http\Controllers\Api\V1\Tag\TagController::class,'status']);

    Route::get('/status/clear',[\App\Http\Controllers\Api\V1\Tag\TagController::class,'clearStatus']);

    Route::get('/me', function () {
        return auth()->user();
    });

    Route::get('/logout', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout']);

    Route::post('/tag', [\App\Http\Controllers\Api\V1\Tag\TagController::class, 'store'])->middleware('limit');

    Route::post('/tag/multiple', [\App\Http\Controllers\Api\V1\Tag\TagController::class, 'storeMultiple']);

    Route::get('/tags', [\App\Http\Controllers\Api\V1\Tag\TagController::class, 'index']);

    Route::get('/tags/top', [\App\Http\Controllers\Api\V1\Tag\TagController::class, 'top']);

    Route::get('/users', [\App\Http\Controllers\Api\V1\User\UserController::class, 'getUsers']);

    Route::get('/quota', [\App\Http\Controllers\Api\V1\Tag\TagController::class, 'remain']);

    Route::get('/history', [\App\Http\Controllers\Api\V1\Tag\TagController::class, 'history']);

    Route::get('/history/last', [\App\Http\Controllers\Api\V1\Tag\TagController::class, 'lastHistory']);

    Route::get('/history/clear', [\App\Http\Controllers\Api\V1\Tag\TagController::class, 'clearHistory']);
});

