<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarketController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user/markets', [MarketController::class, 'getUserMarkets']);
    Route::post('/reports/filter', [MarketController::class, 'applyFilters']);
    Route::get('/reports/job-bookings', [\App\Http\Controllers\ReportController::class, 'jobBookings']);
    Route::get('/reports/conversion-funnel', [\App\Http\Controllers\ReportController::class, 'conversionFunnel']);
});
