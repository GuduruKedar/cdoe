<?php

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
