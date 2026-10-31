<?php

use App\Http\Controllers\GeneralController;
use App\Http\Controllers\RoleSwitchController;
use Illuminate\Support\Facades\Route;
use Modules\Admissions\Http\Controllers\StudentAdmissionController;

Route::redirect('/', '/login');

Route::get('/admissions/stdadmissioninfo',
    [StudentAdmissionController::class, 'info']
)->name('admissions.stdadmissioninfo');

Route::get('/admissions/info',
    [StudentAdmissionController::class, 'info']
)->name('admissions.info');

Route::post('/switch-role/{roleId}',
    [RoleSwitchController::class, 'switch']
)->name('switch.role');

// General Regulations & Academic Documents Hub (Accessible by ALL roles, managed by Main Admin)
Route::middleware(['web'])->group(function () {
    Route::get('/general', [GeneralController::class, 'index'])->name('general.index');
    Route::get('/general/index', [GeneralController::class, 'index']);
    Route::get('/general/manage', [GeneralController::class, 'manage'])->name('general.manage');
    Route::post('/general/upload', [GeneralController::class, 'store'])->name('general.store');
    Route::get('/general/document/{document}/download', [GeneralController::class, 'download'])->name('general.download');
    Route::get('/general/document/{document}/preview', [GeneralController::class, 'preview'])->name('general.preview');
    Route::delete('/general/document/{document}', [GeneralController::class, 'destroy'])->name('general.destroy');
    Route::get('/general/pprbooks', [GeneralController::class, 'pprBooks'])->name('general.pprbooks');
});
