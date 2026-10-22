<?php

use Illuminate\Support\Facades\Route;
use Modules\Academics\Http\Controllers\AcademicsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('academics', AcademicsController::class)->names('academics');
});
