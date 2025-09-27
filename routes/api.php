<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TabController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

/* Rotas protegidas por Sanctum */
Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('/products', ProductController::class);
    Route::apiResource('/tabs', TabController::class);

    // Rotas específicas para gerenciar produtos nas comandas
    Route::post('/tabs/{tab}/add-product', [TabController::class, 'addProduct']);
    Route::post('/tabs/{tab}/remove-product', [TabController::class, 'removeProduct']);
    Route::post('/tabs/{tab}/close', [TabController::class, 'close']);
    
    // Rotas específicas para gerenciar pagamentos das comandas
    Route::post('/tabs/{tab}/add-payment', [TabController::class, 'addPayment']);
    Route::post('/tabs/{tab}/remove-payment', [TabController::class, 'removePayment']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});
