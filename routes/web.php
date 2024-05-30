<?php

use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckJsonResultsController;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

// App
Route::redirect('/', '/admin');
Route::redirect('/login', '/admin/login')->name('login'); // filament needs a 'login' route after

// Health
Route::get('health', HealthCheckResultsController::class);
Route::get('health.json', HealthCheckJsonResultsController::class);
