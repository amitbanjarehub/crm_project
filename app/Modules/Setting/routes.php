<?php

use App\Modules\Setting\Controllers\LeadOptionController;
use App\Modules\Setting\Controllers\SettingController;
use App\Modules\Setting\Controllers\TaskOptionController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/lead-options',
    [
        LeadOptionController::class,
        'index',
    ]
)
    ->middleware(
        'permission:settings.view'
    )
    ->name('lead-options.index');

Route::post(
    '/lead-options/statuses',
    [
        LeadOptionController::class,
        'storeStatus',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('lead-statuses.store');

Route::put(
    '/lead-options/statuses/{leadStatus}',
    [
        LeadOptionController::class,
        'updateStatus',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('lead-statuses.update');

Route::delete(
    '/lead-options/statuses/{leadStatus}',
    [
        LeadOptionController::class,
        'destroyStatus',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('lead-statuses.destroy');

Route::post(
    '/lead-options/priorities',
    [
        LeadOptionController::class,
        'storePriority',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('lead-priorities.store');

Route::put(
    '/lead-options/priorities/{leadPriority}',
    [
        LeadOptionController::class,
        'updatePriority',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('lead-priorities.update');

Route::delete(
    '/lead-options/priorities/{leadPriority}',
    [
        LeadOptionController::class,
        'destroyPriority',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('lead-priorities.destroy');

Route::get(
    '/task-options',
    [
        TaskOptionController::class,
        'index',
    ]
)
    ->middleware(
        'permission:settings.view'
    )
    ->name('task-options.index');

Route::post(
    '/task-options/statuses',
    [
        TaskOptionController::class,
        'storeStatus',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('task-statuses.store');

Route::put(
    '/task-options/statuses/{taskStatus}',
    [
        TaskOptionController::class,
        'updateStatus',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('task-statuses.update');

Route::delete(
    '/task-options/statuses/{taskStatus}',
    [
        TaskOptionController::class,
        'destroyStatus',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('task-statuses.destroy');

Route::post(
    '/task-options/priorities',
    [
        TaskOptionController::class,
        'storePriority',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('task-priorities.store');

Route::put(
    '/task-options/priorities/{taskPriority}',
    [
        TaskOptionController::class,
        'updatePriority',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('task-priorities.update');

Route::delete(
    '/task-options/priorities/{taskPriority}',
    [
        TaskOptionController::class,
        'destroyPriority',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('task-priorities.destroy');

Route::get(
    '/',
    [
        SettingController::class,
        'index',
    ]
)
    ->middleware(
        'permission:settings.view'
    )
    ->name('index');

Route::put(
    '/',
    [
        SettingController::class,
        'update',
    ]
)
    ->middleware(
        'permission:settings.update'
    )
    ->name('update');