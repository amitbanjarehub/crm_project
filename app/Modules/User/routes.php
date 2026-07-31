<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Controllers\UserController;

// Route::get('/', [UserController::class, 'index'])->name('index');

// Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');

// Route::get('/create', [UserController::class, 'create'])->name('create');

// Route::post('/', [UserController::class, 'store'])->name('store');

// Route::put('/{user}', [UserController::class, 'update'])->name('update');

// Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

Route::get('/', [UserController::class, 'index'])
    ->middleware('permission:users.view')
    ->name('index');

Route::get('/create', [UserController::class, 'create'])
    ->middleware('permission:users.create')
    ->name('create');

Route::post('/', [UserController::class, 'store'])
    ->middleware('permission:users.create')
    ->name('store');

Route::patch('/{user}/status', [UserController::class, 'updateStatus'])
    ->middleware('permission:users.toggle_status')
    ->name('status.update');

Route::get('/{user}/edit', [UserController::class, 'edit'])
    ->middleware('permission:users.edit')
    ->name('edit');

Route::put('/{user}', [UserController::class, 'update'])
    ->middleware('permission:users.edit')
    ->name('update');

Route::delete('/{user}', [UserController::class, 'destroy'])
    ->middleware('permission:users.delete')
    ->name('destroy');