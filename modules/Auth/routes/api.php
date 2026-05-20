<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public authentication endpoints
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Everything below requires a valid Sanctum bearer token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});
