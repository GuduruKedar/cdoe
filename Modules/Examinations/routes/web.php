<?php

use Illuminate\Support\Facades\Route;
use Modules\Examinations\Http\Controllers\CgpaMarksDownloadController;
use Modules\Examinations\Http\Controllers\CgpaMarksUploadController;
use Modules\Examinations\Http\Controllers\ExamResultsController;

Route::middleware(['web'])->group(function () {

    // ================= SPECIFIC EXAMINATIONS ROUTES =================
    Route::get('examinations/stdmarks', [ExamResultsController::class, 'stdMarks'])
        ->name('examinations.stdmarks');

    Route::get('examinations/mmview', [ExamResultsController::class, 'marksMemoList'])
        ->name('examinations.mmview');

    // ================= CGPA REPORT DOWNLOAD =================
    Route::get('examinations/cgpamarks', [CgpaMarksDownloadController::class, 'index'])
        ->name('examinations.cgpamarks');

    Route::get('examinations/cgpamarks/export', [CgpaMarksDownloadController::class, 'exportCsv'])
        ->name('examinations.cgpamarks.export');

    // ================= CGPA MARKS UPLOAD =================
    Route::get('examinations/cgpamarkupload', [CgpaMarksUploadController::class, 'index'])
        ->name('examinations.cgpamarkupload');
    Route::post('examinations/cgpamarkupload', [CgpaMarksUploadController::class, 'upload'])
        ->name('examinations.cgpamarkupload.post');
    Route::get('examinations/cgpamarkupload/sample-template', [CgpaMarksUploadController::class, 'sampleTemplate'])
        ->name('examinations.cgpamarkupload.sample');

    Route::get('examinations/marks-memo/{memo}',
        [ExamResultsController::class, 'marksMemo'])
        ->name('examinations.results.memo');

    Route::get('examinations/marks memo/{memo}',
        [ExamResultsController::class, 'marksMemo']);

    Route::get('examinations/marks_memo/{memo}',
        [ExamResultsController::class, 'marksMemo']);

    // Database menu aliases (controller_ref compatibility)
    Route::get('examinations/cgpamarkuploadsd', [CgpaMarksUploadController::class, 'index']);
    Route::post('examinations/cgpamarkuploadsd', [CgpaMarksUploadController::class, 'upload']);
    Route::get('examinations/cgpamarkdownloadsd', [CgpaMarksDownloadController::class, 'index']);

    // Resource catch-all
    Route::resource('examinations', ExamResultsController::class)
        ->names('examinations');
});
