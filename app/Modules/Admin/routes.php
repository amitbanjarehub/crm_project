<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Controllers\DashboardController;

// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/', [DashboardController::class, 'index'])
    ->middleware('permission:dashboard.view')
    ->name('dashboard');