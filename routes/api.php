<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\PostController;

// Регистрация и логин
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// Публичный доступ
Route::get('/posts', [PostController::class, 'index']); // Все посты

// Защищённые маршруты
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);     // Создание поста
    Route::get('/myposts', [PostController::class, 'myPosts']);  // Мои посты
    Route::get('/protected', function () {
        return response()->json(['message' => 'OK'], 200);
    });
});
