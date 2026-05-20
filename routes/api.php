<?php

use Illuminate\Support\Facades\Route;

// Everything below requires a valid Sanctum bearer token
Route::middleware('auth:sanctum')->group(function () {
    //
});
