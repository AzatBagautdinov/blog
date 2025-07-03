<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoginController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->get('/protected', function () {
    return response()->json(['message' => 'OK'], 200);
});
