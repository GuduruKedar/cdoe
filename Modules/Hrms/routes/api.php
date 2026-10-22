<?php

use Illuminate\Support\Facades\Route;
use Modules\Hrms\Http\Controllers\HrmsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('hrms', HrmsController::class)->names('hrms');
});
