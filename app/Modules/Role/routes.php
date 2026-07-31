<?php

use App\Modules\Role\Controllers\RoleController;
use App\Modules\Role\Controllers\RolePermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [
    RoleController::class,
    'index',
])
    ->middleware('permission:roles.view')
    ->name('index');

/*
 * Ye static routes dynamic {role} route se pehle honi chahiye.
 */
Route::get('/create', [
    RoleController::class,
    'create',
])
    ->middleware('permission:roles.create')
    ->name('create');

Route::post('/', [
    RoleController::class,
    'store',
])
    ->middleware('permission:roles.create')
    ->name('store');

Route::get('/{role}/permissions', [
    RolePermissionController::class,
    'edit',
])
    ->middleware('permission:roles.manage_permissions')
    ->name('permissions.edit');

Route::put('/{role}/permissions', [
    RolePermissionController::class,
    'update',
])
    ->middleware('permission:roles.manage_permissions')
    ->name('permissions.update');