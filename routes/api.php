<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

// Public authentication endpoints
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Everything below requires a valid Sanctum bearer token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Must be declared before the apiResource so "me" is not matched
    // as a {loan} route-model-binding parameter.
    Route::get('loans/me', [LoanController::class, 'myLoans']);
    Route::apiResource('loans', LoanController::class);
});
