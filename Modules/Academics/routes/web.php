<?php

use Illuminate\Support\Facades\Route;
use Modules\Academics\Http\Controllers\AcademicController;
use Modules\Academics\Http\Controllers\CourseRegistrationDownloadController;
use Modules\Academics\Http\Controllers\SubjectListController;
use Modules\Examinations\Http\Controllers\ExamResultsController;

Route::middleware(['web'])->group(function () {
    Route::get('/academics', [AcademicController::class, 'index'])->name('academics');
    Route::get('/academics/result', [ExamResultsController::class, 'create'])->name('academics.create');

    // ================= 1. COURSE REGISTRATION DOWNLOAD =================
    Route::get('academics/coursereg', [CourseRegistrationDownloadController::class, 'index'])
        ->name('academics.coursereg');

    Route::get('academics/coursereg/export', [CourseRegistrationDownloadController::class, 'exportCsv'])
        ->name('academics.coursereg.export');

    // ================= 2. SUBJECT LIST & SUBJECTS VIEW =================
    Route::get('academics/subjectsview', [SubjectListController::class, 'index'])
        ->name('academics.subjectsview');

    Route::get('academics/subjectsview/export', [SubjectListController::class, 'exportCsv'])
        ->name('academics.subjectsview.export');

    Route::get('academics/subjectlist', [SubjectListController::class, 'index'])
        ->name('academics.subjectlist');

    Route::get('academics/subjectlist/export', [SubjectListController::class, 'exportCsv'])
        ->name('academics.subjectlist.export');
});
