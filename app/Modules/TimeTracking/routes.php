<?php

use App\Modules\TimeTracking\Controllers\TimeTrackingController;
use App\Modules\TimeTracking\Controllers\TimeTrackingReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [
    TimeTrackingController::class,
    'index',
])
    ->middleware(
        'permission:time_tracking.view_own'
    )
    ->name('index');

Route::get('/report', [
    TimeTrackingReportController::class,
    'index',
])
    ->middleware(
        'permission:time_tracking.view_own'
    )
    ->name('report');

Route::get('/current', [
    TimeTrackingController::class,
    'current',
])
    ->middleware(
        'permission:time_tracking.use'
    )
    ->name('current');

Route::post('/task/{task}/start', [
    TimeTrackingController::class,
    'start',
])
    ->middleware(
        'permission:time_tracking.use'
    )
    ->name('start');

Route::post('/pause', [
    TimeTrackingController::class,
    'pause',
])
    ->middleware(
        'permission:time_tracking.use'
    )
    ->name('pause');

Route::post('/resume', [
    TimeTrackingController::class,
    'resume',
])
    ->middleware(
        'permission:time_tracking.use'
    )
    ->name('resume');

Route::post('/stop', [
    TimeTrackingController::class,
    'stop',
])
    ->middleware(
        'permission:time_tracking.use'
    )
    ->name('stop');