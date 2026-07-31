<?php

use App\Modules\Task\Controllers\TaskAttachmentController;
use App\Modules\Task\Controllers\TaskCommentController;
use App\Modules\Task\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Modules\Task\Controllers\TaskDependencyController;
use App\Modules\Task\Controllers\TaskImportExportController;

Route::get('/', [TaskController::class, 'index'])
    ->middleware('permission:tasks.view')
    ->name('index');

Route::get('/my-tasks', [
    TaskController::class,
    'myTasks',
])
    ->middleware('permission:tasks.view')
    ->name('my');

/*
 * Task Excel Import and Export
 */

Route::get(
    '/excel/import',
    [
        TaskImportExportController::class,
        'importForm',
    ]
)
    ->middleware(
        'permission:tasks.import'
    )
    ->name('import.form');

Route::post(
    '/excel/import',
    [
        TaskImportExportController::class,
        'import',
    ]
)
    ->middleware(
        'permission:tasks.import'
    )
    ->name('import.store');

Route::get(
    '/excel/template',
    [
        TaskImportExportController::class,
        'downloadTemplate',
    ]
)
    ->middleware(
        'permission:tasks.import'
    )
    ->name('import.template');

Route::get(
    '/excel/export',
    [
        TaskImportExportController::class,
        'export',
    ]
)
    ->middleware(
        'permission:tasks.export'
    )
    ->name('export');

Route::get('/service/{projectService}/create', [
    TaskController::class,
    'create',
])
    ->middleware('permission:tasks.create')
    ->name('create');

Route::post('/service/{projectService}', [
    TaskController::class,
    'store',
])
    ->middleware('permission:tasks.create')
    ->name('store');

Route::patch('/{task}/status', [
    TaskController::class,
    'updateStatus',
])
    ->middleware('permission:tasks.update_status')
    ->name('status.update');

Route::post('/{task}/submit-review', [
    TaskController::class,
    'submitReview',
])
    ->middleware('permission:tasks.update_status')
    ->name('submit-review');

Route::post('/{task}/approve', [
    TaskController::class,
    'approve',
])
    ->middleware('permission:tasks.review')
    ->name('approve');

Route::post('/{task}/reject', [
    TaskController::class,
    'reject',
])
    ->middleware('permission:tasks.review')
    ->name('reject');

Route::post('/{task}/comments', [
    TaskCommentController::class,
    'store',
])
    ->middleware('permission:task_comments.create')
    ->name('comments.store');

Route::delete('/{task}/comments/{comment}', [
    TaskCommentController::class,
    'destroy',
])
    ->middleware('permission:task_comments.delete')
    ->name('comments.destroy');

Route::post('/{task}/attachments', [
    TaskAttachmentController::class,
    'store',
])
    ->middleware('permission:task_attachments.upload')
    ->name('attachments.store');

Route::get('/{task}/attachments/{attachment}', [
    TaskAttachmentController::class,
    'download',
])
    ->middleware('permission:task_attachments.download')
    ->name('attachments.download');

Route::delete('/{task}/attachments/{attachment}', [
    TaskAttachmentController::class,
    'destroy',
])
    ->middleware('permission:task_attachments.delete')
    ->name('attachments.destroy');

Route::post('/{task}/dependencies', [
    TaskDependencyController::class,
    'store',
])
    ->middleware(
        'permission:tasks.manage_dependencies'
    )
    ->name('dependencies.store');

Route::delete(
    '/{task}/dependencies/{prerequisite}',
    [
        TaskDependencyController::class,
        'destroy',
    ]
)
    ->middleware(
        'permission:tasks.manage_dependencies'
    )
    ->name('dependencies.destroy');


Route::get('/{task}/edit', [
    TaskController::class,
    'edit',
])
    ->middleware('permission:tasks.edit')
    ->name('edit');

Route::put('/{task}', [
    TaskController::class,
    'update',
])
    ->middleware('permission:tasks.edit')
    ->name('update');

Route::delete('/{task}', [
    TaskController::class,
    'destroy',
])
    ->middleware('permission:tasks.delete')
    ->name('destroy');

Route::get('/{task}', [
    TaskController::class,
    'show',
])
    ->middleware('permission:tasks.view')
    ->name('show');