<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('api')->prefix('v1')->group(function () {
    Route::get('natal/calculator', [\App\Http\Api\NatalCalculatorController::class, 'calculate']);
});
