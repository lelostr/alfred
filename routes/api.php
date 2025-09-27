<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Rotas protegidas por Sanctum */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});