<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

/* Rotas protegidas por Sanctum */
Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('/products', ProductController::class);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});
