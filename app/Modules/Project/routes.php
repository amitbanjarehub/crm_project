<?php

use App\Modules\Project\Controllers\ProjectController;
use App\Modules\Project\Controllers\ProjectMemberController;
use App\Modules\Project\Controllers\ProjectServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProjectController::class, 'index'])
    ->middleware('permission:projects.view')
    ->name('index');

Route::get('/create', [ProjectController::class, 'create'])
    ->middleware('permission:projects.create')
    ->name('create');

Route::post('/', [ProjectController::class, 'store'])
    ->middleware('permission:projects.create')
    ->name('store');

Route::post('/{project}/members', [
    ProjectMemberController::class,
    'store',
])
    ->middleware('permission:projects.manage_members')
    ->name('members.store');

Route::delete('/{project}/members/{user}', [
    ProjectMemberController::class,
    'destroy',
])
    ->middleware('permission:projects.manage_members')
    ->name('members.destroy');

Route::post('/{project}/services', [
    ProjectServiceController::class,
    'store',
])
    ->middleware('permission:project_services.create')
    ->name('services.store');

Route::get('/{project}/services/{projectService}/edit', [
    ProjectServiceController::class,
    'edit',
])
    ->middleware('permission:project_services.edit')
    ->name('services.edit');

Route::put('/{project}/services/{projectService}', [
    ProjectServiceController::class,
    'update',
])
    ->middleware('permission:project_services.edit')
    ->name('services.update');

Route::delete('/{project}/services/{projectService}', [
    ProjectServiceController::class,
    'destroy',
])
    ->middleware('permission:project_services.delete')
    ->name('services.destroy');

Route::post('/{project}/complete', [
    ProjectController::class,
    'complete',
])
    ->middleware('permission:projects.complete')
    ->name('complete');

Route::get('/{project}/edit', [
    ProjectController::class,
    'edit',
])
    ->middleware('permission:projects.edit')
    ->name('edit');

Route::put('/{project}', [
    ProjectController::class,
    'update',
])
    ->middleware('permission:projects.edit')
    ->name('update');

Route::delete('/{project}', [
    ProjectController::class,
    'destroy',
])
    ->middleware('permission:projects.delete')
    ->name('destroy');

Route::get('/{project}', [
    ProjectController::class,
    'show',
])
    ->middleware('permission:projects.view')
    ->name('show');


    