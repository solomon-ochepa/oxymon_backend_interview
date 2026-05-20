<?php

use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

// Everything below requires a valid Sanctum bearer token
Route::middleware('auth:sanctum')->group(function () {
    // Must be declared before the apiResource so "me" is not matched
    // as a {loan} route-model-binding parameter.
    Route::get('loans/me', [LoanController::class, 'myLoans']);
    Route::apiResource('loans', LoanController::class);
});
