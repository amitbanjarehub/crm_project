<?php

use App\Modules\Permission\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('permission::index');
// })->name('index');

Route::get('/', [PermissionController::class, 'index'])
    ->middleware('permission:permissions.view')
    ->name('index');