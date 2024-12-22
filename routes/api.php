<?php

use App\Facades\MessageFixer;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\DistrictController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\OutletController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProvinceController;
use App\Http\Controllers\API\RegencyController;
use App\Http\Controllers\API\VillageController;
use App\Http\Middleware\XAuthMiddleware;
use App\Http\Middleware\XSignatureMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login-checker', function (Request $request) {
    return MessageFixer::error("Unauthorized");
})->name('login');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signature', [AuthController::class, 'signature']);

    Route::middleware([XAuthMiddleware::class, XSignatureMiddleware::class])->group(function () {
        Route::post('/me', [AuthController::class, 'me']);
    });
});

Route::middleware([XSignatureMiddleware::class])->group(function () {
    Route::post('/category', [CategoryController::class, 'index']);
    Route::post('/product', [ProductController::class, 'index']);

    Route::prefix('region')->group(function () {
        Route::post('/province', [ProvinceController::class, 'index']);
        Route::post('/regency', [RegencyController::class, 'index']);
        Route::post('/district', [DistrictController::class, 'index']);
        Route::post('/village', [VillageController::class, 'index']);
    });
});

Route::middleware([XAuthMiddleware::class, XSignatureMiddleware::class])->group(function () {
    Route::prefix('product')->group(function () {
        Route::post('/store', [ProductController::class, 'store']);
        Route::post('/update', [ProductController::class, 'update']);
        Route::post('/delete', [ProductController::class, 'destroy']);
    });

    Route::prefix('outlet')->group(function () {
        Route::post('/register', [OutletController::class, 'register']);
        Route::post('/show', [OutletController::class, 'show']);
        Route::post('/operational-hour', [OutletController::class, 'updateOperationalHour']);
        Route::post('/update', [OutletController::class, 'update']);
    });

    Route::prefix('address')->group(function () {
        Route::post('/', [AddressController::class, 'index']);
        Route::post('/store', [AddressController::class, 'store']);
        Route::post('/update', [AddressController::class, 'update']);
        Route::post('/delete', [AddressController::class, 'destroy']);
    });

    Route::prefix('order')->group(function () {
        Route::post('/', [OrderController::class, 'index']);
        Route::post('/store', [OrderController::class, 'store']);
        Route::post('/update-status', [OrderController::class, 'updateStatus']);
    });
});
