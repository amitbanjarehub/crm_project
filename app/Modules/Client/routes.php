<?php

use App\Modules\Client\Controllers\ClientController;
use App\Modules\Client\Controllers\ClientImportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ClientController::class, 'index'])
    ->middleware('permission:clients.view')
    ->name('index');

Route::get('/create', [ClientController::class, 'create'])
    ->middleware('permission:clients.create')
    ->name('create');

Route::post('/', [ClientController::class, 'store'])
    ->middleware('permission:clients.create')
    ->name('store');

/*
 * Client Excel Import and Export
 *
 * Ye static routes /{client} se pehle
 * define honi chahiye.
 */

Route::get(
    '/excel/import',
    [
        ClientImportExportController::class,
        'importForm',
    ]
)
    ->middleware(
        'permission:clients.import'
    )
    ->name('import.form');

Route::post(
    '/excel/import',
    [
        ClientImportExportController::class,
        'import',
    ]
)
    ->middleware(
        'permission:clients.import'
    )
    ->name('import.store');

Route::get(
    '/excel/template',
    [
        ClientImportExportController::class,
        'downloadTemplate',
    ]
)
    ->middleware(
        'permission:clients.import'
    )
    ->name('import.template');

Route::get(
    '/excel/export',
    [
        ClientImportExportController::class,
        'export',
    ]
)
    ->middleware(
        'permission:clients.export'
    )
    ->name('export');

Route::get('/{client}/edit', [
    ClientController::class,
    'edit'
])
    ->middleware('permission:clients.edit')
    ->name('edit');

Route::put('/{client}', [
    ClientController::class,
    'update'
])
    ->middleware('permission:clients.edit')
    ->name('update');

Route::delete('/{client}', [
    ClientController::class,
    'destroy'
])
    ->middleware('permission:clients.delete')
    ->name('destroy');

Route::get('/{client}', [
    ClientController::class,
    'show'
])
    ->middleware('permission:clients.view')
    ->name('show');