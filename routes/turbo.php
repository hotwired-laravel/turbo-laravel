<?php

use HotwiredLaravel\TurboLaravel\Http\Controllers\TurboNativeNavigationController;
use Illuminate\Support\Facades\Route;

Route::get('recede_historical_location', [TurboNativeNavigationController::class, 'recede'])->name('turbo_recede_historical_location');
Route::get('resume_historical_location', [TurboNativeNavigationController::class, 'resume'])->name('turbo_resume_historical_location');
Route::get('refresh_historical_location', [TurboNativeNavigationController::class, 'refresh'])->name('turbo_refresh_historical_location');
