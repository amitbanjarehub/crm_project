<?php

use App\Modules\Report\Controllers\ExecutiveDashboardController;
use App\Modules\Report\Controllers\ProjectReportController;
use App\Modules\Report\Controllers\FollowUpReportController;
use App\Modules\Report\Controllers\LeadReportController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/executive-dashboard',
    [
        ExecutiveDashboardController::class,
        'index',
    ]
)
    ->middleware(
        'permission:reports.executive.view'
    )
    ->name('executive');

Route::get(
    '/leads',
    [
        LeadReportController::class,
        'index',
    ]
)
    ->middleware(
        'permission:reports.leads.view'
    )
    ->name('leads.index');

Route::get(
    '/follow-ups',
    [
        FollowUpReportController::class,
        'index',
    ]
)
    ->middleware(
        'permission:reports.followups.view'
    )
    ->name('followups.index');

Route::get(
    '/projects',
    [
        ProjectReportController::class,
        'index',
    ]
)
    ->middleware(
        'permission:reports.projects.view'
    )
    ->name('projects.index');

Route::get(
    '/projects/{project}',
    [
        ProjectReportController::class,
        'show',
    ]
)
    ->middleware(
        'permission:reports.projects.view'
    )
    ->name('projects.show');