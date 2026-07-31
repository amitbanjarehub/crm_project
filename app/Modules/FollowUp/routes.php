<?php

use App\Modules\FollowUp\Controllers\FollowUpController;
use App\Modules\FollowUp\Controllers\FollowUpImportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FollowUpController::class, 'index'])
    ->middleware('permission:follow_ups.view')
    ->name('index');

/*
 * Follow-up Excel Import and Export.
 *
 * Ye static routes dynamic /{followUp}
 * route se pehle honi chahiye.
 */

Route::get(
    '/excel/import',
    [
        FollowUpImportExportController::class,
        'importForm',
    ]
)
    ->middleware(
        'permission:follow_ups.import'
    )
    ->name('import.form');

Route::post(
    '/excel/import',
    [
        FollowUpImportExportController::class,
        'import',
    ]
)
    ->middleware(
        'permission:follow_ups.import'
    )
    ->name('import.store');

Route::get(
    '/excel/template',
    [
        FollowUpImportExportController::class,
        'downloadTemplate',
    ]
)
    ->middleware(
        'permission:follow_ups.import'
    )
    ->name('import.template');

Route::get(
    '/excel/export',
    [
        FollowUpImportExportController::class,
        'export',
    ]
)
    ->middleware(
        'permission:follow_ups.export'
    )
    ->name('export');



Route::get('/lead/{lead}/create', [
    FollowUpController::class,
    'create'
])
    ->middleware('permission:follow_ups.create')
    ->name('create');

Route::post('/lead/{lead}', [
    FollowUpController::class,
    'store'
])
    ->middleware('permission:follow_ups.create')
    ->name('store');



Route::get('/{followUp}/edit', [
    FollowUpController::class,
    'edit'
])
    ->middleware('permission:follow_ups.edit')
    ->name('edit');

Route::put('/{followUp}', [
    FollowUpController::class,
    'update'
])
    ->middleware('permission:follow_ups.edit')
    ->name('update');

Route::delete('/{followUp}', [
    FollowUpController::class,
    'destroy'
])
    ->middleware('permission:follow_ups.delete')
    ->name('destroy');