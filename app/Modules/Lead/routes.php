<?php

use App\Modules\Lead\Controllers\LeadController;
use App\Modules\Lead\Controllers\LeadConversionController;
use App\Modules\Lead\Controllers\LeadImportExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LeadController::class, 'index'])
    ->middleware('permission:leads.view')
    ->name('index');

Route::get('/create', [LeadController::class, 'create'])
    ->middleware('permission:leads.create')
    ->name('create');

Route::post('/', [LeadController::class, 'store'])
    ->middleware('permission:leads.create')
    ->name('store');

/*
 * Lead Excel Import and Export
 */

Route::get(
    '/excel/import',
    [
        LeadImportExportController::class,
        'importForm',
    ]
)
    ->middleware(
        'permission:leads.import'
    )
    ->name('import.form');

Route::post(
    '/excel/import',
    [
        LeadImportExportController::class,
        'import',
    ]
)
    ->middleware(
        'permission:leads.import'
    )
    ->name('import.store');

Route::get(
    '/excel/template',
    [
        LeadImportExportController::class,
        'downloadTemplate',
    ]
)
    ->middleware(
        'permission:leads.import'
    )
    ->name('import.template');

Route::get(
    '/excel/export',
    [
        LeadImportExportController::class,
        'export',
    ]
)
    ->middleware(
        'permission:leads.export'
    )
    ->name('export');

Route::post('/{lead}/convert', [
    LeadConversionController::class,
    'store'
])
    ->middleware('permission:leads.convert')
    ->name('convert');

Route::patch('/{lead}/status', [
    LeadController::class,
    'updateStatus'
])
    ->middleware('permission:leads.edit')
    ->name('status.update');

Route::get('/{lead}/edit', [
    LeadController::class,
    'edit'
])
    ->middleware('permission:leads.edit')
    ->name('edit');

Route::put('/{lead}', [
    LeadController::class,
    'update'
])
    ->middleware('permission:leads.edit')
    ->name('update');

Route::delete('/{lead}', [
    LeadController::class,
    'destroy'
])
    ->middleware('permission:leads.delete')
    ->name('destroy');

Route::get('/{lead}', [
    LeadController::class,
    'show'
])
    ->middleware('permission:leads.view')
    ->name('show');