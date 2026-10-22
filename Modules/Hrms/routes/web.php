<?php

use Illuminate\Support\Facades\Route;
use Modules\Hrms\Http\Controllers\ActivityLogController;
use Modules\Hrms\Http\Controllers\EmployeeCreationController;
use Modules\Hrms\Http\Controllers\EmployeeSearchController;
use Modules\Hrms\Http\Controllers\HrmsController;

Route::middleware(['web'])->group(function () {
    // Activity Logs (Menu item: hrms/activitylogs)
    Route::get('hrms/activitylogs', [ActivityLogController::class, 'index'])
        ->name('hrms.activitylogs');
    Route::get('hrms/ActivityLogs', [ActivityLogController::class, 'index']);
    Route::get('hrms/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('hrms/activitylogs/{id}', [ActivityLogController::class, 'show'])
        ->name('hrms.activitylogs.show');

    // Employee Search (Menu item: hrms/EmpSearch)
    Route::get('hrms/EmpSearch', [EmployeeSearchController::class, 'index'])
        ->name('hrms.empsearch');
    Route::get('hrms/empsearch', [EmployeeSearchController::class, 'index']);

    // Employee Creation (Menu item: hrms/EmpCreation)
    Route::get('hrms/EmpCreation', [EmployeeCreationController::class, 'create'])
        ->name('hrms.empcreation');
    Route::get('hrms/empcreation', [EmployeeCreationController::class, 'create']);
    Route::post('hrms/EmpCreation', [EmployeeCreationController::class, 'store'])
        ->name('hrms.empcreation.store');
    Route::post('hrms/empcreation', [EmployeeCreationController::class, 'store']);

    // Student Search shortcut in HRMS menu (hrms/viewStdProfileDetailsEmp)
    Route::get('hrms/viewStdProfileDetailsEmp', function () {
        return redirect()->route('admissions.stdsearch');
    })->name('hrms.stdsearch.redirect');

    Route::resource('hrms', HrmsController::class)->names('hrms');
});
