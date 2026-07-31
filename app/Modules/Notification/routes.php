<?php

use App\Modules\Notification\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [
    NotificationController::class,
    'index',
])->name('index');

/*
 * AJAX near real-time notification polling.
 *
 * Final URL:
 * GET /notification/poll
 */
Route::get('/poll', [
    NotificationController::class,
    'poll',
])->name('poll');

/*
 * Static route dynamic notification route se pehle.
 */
Route::post('/read-all', [
    NotificationController::class,
    'markAllAsRead',
])->name('read-all');

Route::get('/{notification}/open', [
    NotificationController::class,
    'open',
])->name('open');

Route::patch('/{notification}/read', [
    NotificationController::class,
    'markAsRead',
])->name('read');

Route::delete('/{notification}', [
    NotificationController::class,
    'destroy',
])->name('destroy');