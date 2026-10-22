<?php

use Illuminate\Support\Facades\Route;
use Modules\Visa\Http\Controllers\VisaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('visas', VisaController::class)->names('visa');
});
