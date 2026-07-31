<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified', 'active.user'])->name('dashboard');



$modules = [
    'Admin',
    'User',
    'Role',
    'Permission',
    'Lead',
    'FollowUp',
    'Client',
    'Project',
    'Task',
    'Notification',
    'TimeTracking',
    'Report',
    'Setting',
];

foreach ($modules as $module) {
    $routeFile = app_path("Modules/{$module}/routes.php");

    if (file_exists($routeFile)) {
        Route::middleware(['auth', 'active.user'])
            ->prefix(strtolower($module))
            ->name(strtolower($module) . '.')
            ->group($routeFile);
    }
}

require __DIR__ . '/auth.php';