<?php

use Illuminate\Support\Facades\Route;
use Modules\Admissions\Http\Controllers\AdmissionsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('admissions', AdmissionsController::class)->names('admissions');
});
