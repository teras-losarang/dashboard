<?php

use App\Facades\MessageFixer;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login-checker', function (Request $request) {
    return MessageFixer::error("Unauthorized");
})->name('login');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/me', [AuthController::class, 'me']);
    });
});

Route::post('/category', [CategoryController::class, 'index']);
