<?php

use Illuminate\Support\Facades\Route;
use Modules\Admissions\Http\Controllers\AdmissionDataDownloadController;
use Modules\Admissions\Http\Controllers\DocumentUploadController;
use Modules\Admissions\Http\Controllers\DocumentViewController;
use Modules\Admissions\Http\Controllers\StudentAdmissionController;
use Modules\Admissions\Http\Controllers\StudentSearchController;

Route::middleware(['web'])->group(function () {

    // Student side — regno from session
    Route::get('admissions/info', [StudentAdmissionController::class, 'info'])
        ->name('admissions.info');

    // Employee side — regno from URL
    Route::get('admissions/info/{regno}', [StudentAdmissionController::class, 'info'])
        ->name('admissions.info.byRegno');

    // Student search list
    Route::get('admissions/stdsearch', [StudentSearchController::class, 'index'])
        ->name('admissions.stdsearch');

    // Single student details for modal
    Route::get('admissions/student/{registerNo}', [StudentAdmissionController::class, 'studentDetails'])
        ->name('admissions.student.details');

    // ================= 1. DOCUMENT UPLOAD =================
    Route::get('admissions/docupload', [DocumentUploadController::class, 'index'])
        ->name('admissions.docupload');

    Route::get('admissions/docupload/student-lookup', [DocumentUploadController::class, 'lookupStudent'])
        ->name('admissions.docupload.lookup');

    Route::get('admissions/docupload/student-documents', [DocumentUploadController::class, 'getStudentDocuments'])
        ->name('admissions.docupload.documents');

    Route::post('admissions/docupload/upload', [DocumentUploadController::class, 'upload'])
        ->name('admissions.docupload.upload');

    Route::delete('admissions/docupload/{docId}', [DocumentUploadController::class, 'destroy'])
        ->name('admissions.docupload.destroy');

    // ================= 2. DOCUMENT VIEW / MATRIX =================
    Route::get('admissions/docview', [DocumentViewController::class, 'index'])
        ->name('admissions.docview');

    Route::get('admissions/docview/download/{docId}', [DocumentViewController::class, 'downloadDocument'])
        ->name('admissions.docview.download');

    Route::get('admissions/docview/export', [DocumentViewController::class, 'exportCsv'])
        ->name('admissions.docview.export');

    // ================= 3. ADMISSION DATA DOWNLOAD =================
    Route::get('admissions/datadownload', [AdmissionDataDownloadController::class, 'index'])
        ->name('admissions.datadownload');

    Route::get('admissions/datadownload/export', [AdmissionDataDownloadController::class, 'exportCsv'])
        ->name('admissions.datadownload.export');
});
