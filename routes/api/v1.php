<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BestSellersController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

$throttleLimit = config('auth.throttle_login_limit');
var_dump("ROUTE", $throttleLimit);
Route::prefix('v1')->group(function () use ($throttleLimit) {
    // Public routes
    Route::post('/login', [AuthController::class, 'login'])->middleware("throttle:$throttleLimit,1");

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [UserController::class, 'show']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Other API routes...
        Route::get('/best-sellers', [BestSellersController::class, 'getList']);
    });

});

