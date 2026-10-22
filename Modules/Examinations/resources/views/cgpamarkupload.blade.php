@extends('layouts.app')

@section('page_title', 'CGPA Marks Upload')

@section('content')
<div class="cdoe-marks-upload-container">

    {{-- ================= 1. HERO HEADER ================= --}}
    <div class="marks-hero-card mb-4">
        <div class="marks-hero-content">
            <div class="marks-hero-badge">
                <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Examinations • Academic Performance Module
            </div>
            <h1 class="marks-hero-title">
                CGPA Marks <span class="highlight">Upload</span>
            </h1>
            <p class="marks-hero-desc">
                Bulk upload student semester marks, grade points, SGPA, CGPA percentages, and letter grades using standard CSV templates. Validates register numbers, subject codes, and prevents duplicate grade submissions.
            </p>
            <div class="marks-hero-actions">
                <a href="{{ route('examinations.cgpamarks') }}" class="marks-btn outline">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>
                    <span>CGPA Marks Report / Download</span>
                </a>
                <a href="{{ route('examinations.cgpamarkupload.sample') }}" class="marks-btn accent">
                    <i class="bi bi-download me-1"></i>
                    <span>Download Sample CSV Template</span>
                </a>
            </div>
        </div>
        <div class="marks-hero-aside">
            <div class="marks-status-pill">
                <span class="status-dot"></span>
                <span>Portal Live • RBAC Active</span>
            </div>
        </div>
    </div>

    {{-- ================= ALERTS ================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border-left: 4px solid #10b981 !important; border-radius: 10px;">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" role="alert" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; border-left: 4px solid #ef4444 !important; border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ================= 2. SUMMARY METRICS ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="marks-stat-card">
                <div class="marks-stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                    <i class="bi bi-collection-fill"></i>
                </div>
                <div class="marks-stat-meta">
                    <span class="marks-stat-label">Total Marks Records</span>
                    <h3 class="marks-stat-val">{{ number_format($stats['total_records']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="marks-stat-card">
                <div class="marks-stat-icon" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="marks-stat-meta">
                    <span class="marks-stat-label">Evaluated Students</span>
                    <h3 class="marks-stat-val">{{ number_format($stats['total_students']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="marks-stat-card">
                <div class="marks-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                    <i class="bi bi-calendar2-range-fill"></i>
                </div>
                <div class="marks-stat-meta">
                    <span class="marks-stat-label">Exam Sessions</span>
                    <h3 class="marks-stat-val">{{ number_format($stats['distinct_exams']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="marks-stat-card">
                <div class="marks-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div class="marks-stat-meta">
                    <span class="marks-stat-label">Mapped Courses</span>
                    <h3 class="marks-stat-val">{{ number_format($stats['distinct_subjects']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= 3. UPLOAD INTERFACE & FIELD GUIDE ================= --}}
    <div class="row g-4 mb-4">
        {{-- Upload Form --}}
        <div class="col-lg-7 col-12">
            <div class="marks-panel-card h-100">
                <div class="marks-panel-header">
                    <h5 class="marks-panel-title">
                        <i class="bi bi-cloud-arrow-up-fill me-2 text-primary"></i> 1. Select & Upload CGPA Marks File
                    </h5>
                    <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill">
                        CSV Format Supported
                    </span>
                </div>
                <div class="marks-panel-body">
                    <form method="POST" action="{{ route('examinations.cgpamarkupload.post') }}" enctype="multipart/form-data" id="marksUploadForm">
                        @csrf

                        {{-- Exam Session Override (optional) --}}
                        <div class="mb-3">
                            <label class="form-label marks-form-label">
                                <i class="bi bi-calendar3 me-1"></i> Exam Month-Year (Optional batch default)
                            </label>
                            <input type="text" name="exam_mon_yyyy" class="form-control marks-input" placeholder="e.g. DEC-2025 (used if column is empty in CSV)">
                            <div class="form-text small" style="color: #94a3b8;">
                                If your CSV already has the <code>exam_mon_yyyy</code> column, that value will take priority per student.
                            </div>
                        </div>

                        {{-- Drag & Drop Area --}}
                        <div class="dropzone-box" id="dropzoneArea">
                            <input type="file" name="marks_file" id="marksFileInput" accept=".csv,text/csv" required class="d-none">
                            <div class="dropzone-inner text-center" onclick="document.getElementById('marksFileInput').click()">
                                <div class="dropzone-icon">
                                    <i class="bi bi-cloud-upload-fill"></i>
                                </div>
                                <h6 class="dropzone-text mb-1">Click to browse or drag and drop your marks CSV file here</h6>
                                <p class="dropzone-hint mb-0">Standard CSV files up to 20 MB accepted</p>
                                <div id="selectedFileInfo" class="mt-3 d-none">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill font-monospace" id="fileNameBadge">
                                        <i class="bi bi-file-earmark-check-fill me-1"></i> <span id="fileNameText"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex align-items-center justify-content-between mt-4 gap-2 flex-wrap">
                            <a href="{{ route('examinations.cgpamarkupload.sample') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                                <i class="bi bi-download me-1"></i> Sample Template
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2" id="submitBtn">
                                <i class="bi bi-cloud-check-fill"></i>
                                <span>Process & Upload Marks</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Guide & Requirements --}}
        <div class="col-lg-5 col-12">
            <div class="marks-panel-card h-100">
                <div class="marks-panel-header">
                    <h5 class="marks-panel-title">
                        <i class="bi bi-info-circle-fill me-2 text-info"></i> 2. Template Column Reference
                    </h5>
                    <a href="{{ route('examinations.cgpamarkupload.sample') }}" class="small text-decoration-none text-primary fw-semibold">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Sample
                    </a>
                </div>
                <div class="marks-panel-body">
                    <p class="small text-muted mb-3">
                        The CSV file must contain the following required headers. Any extra columns will be ignored safely.
                    </p>
                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-borderless marks-guide-table">
                            <thead>
                                <tr>
                                    <th>Header</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>regno</code></td>
                                    <td>String</td>
                                    <td><span class="badge bg-danger-subtle text-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>subcode</code></td>
                                    <td>String</td>
                                    <td><span class="badge bg-danger-subtle text-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>mmno</code></td>
                                    <td>String</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">Auto</span></td>
                                </tr>
                                <tr>
                                    <td><code>grade_points</code></td>
                                    <td>Decimal (0–10)</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>letter_grade</code></td>
                                    <td>O, A, B, C, F</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>sgpa_perc</code></td>
                                    <td>Decimal</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>cgpa_perc</code></td>
                                    <td>Decimal</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>division_name</code></td>
                                    <td>Text</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= 4. RECENT MARKS RECORDS TABLE ================= --}}
    <div class="marks-panel-card">
        <div class="marks-panel-header">
            <div>
                <h5 class="marks-panel-title mb-1">
                    <i class="bi bi-clock-history me-2 text-warning"></i> Recent Marks Records Preview
                </h5>
                <span class="text-muted small">Showing the latest verified entries in the examination marks database</span>
            </div>
            <a href="{{ route('examinations.cgpamarks') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-arrow-up-right me-1"></i> Full Marks Directory
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 marks-data-table">
                <thead>
                    <tr>
                        <th>Register No</th>
                        <th>Student Name</th>
                        <th>Exam Session</th>
                        <th>Subject Code</th>
                        <th>Type</th>
                        <th class="text-center">Earned / Total</th>
                        <th class="text-center">Grade Points</th>
                        <th class="text-center">Letter Grade</th>
                        <th class="text-center">CGPA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentMarks as $rm)
                        <tr>
                            <td>
                                <span class="fw-bold font-monospace text-primary">{{ $rm->regno }}</span>
                            </td>
                            <td>{{ $rm->student_name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $rm->exam_mon_yyyy }}</span>
                            </td>
                            <td><span class="font-monospace fw-semibold">{{ $rm->subcode }}</span></td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $rm->mm_type ?? 'R' }}</span>
                            </td>
                            <td class="text-center font-monospace">
                                {{ $rm->total_earned ?? '-' }} / {{ $rm->total_conducted ?? '-' }}
                            </td>
                            <td class="text-center font-monospace fw-bold text-success">
                                {{ is_numeric($rm->grade_points) ? number_format((float) $rm->grade_points, 1) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($rm->letter_grade === 'O' || $rm->letter_grade === 'S')
                                    <span class="badge bg-success-subtle text-success fw-bold px-2">{{ $rm->letter_grade }}</span>
                                @elseif($rm->letter_grade === 'A' || $rm->letter_grade === 'B')
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2">{{ $rm->letter_grade }}</span>
                                @elseif($rm->letter_grade === 'F')
                                    <span class="badge bg-danger-subtle text-danger fw-bold px-2">{{ $rm->letter_grade }}</span>
                                @else
                                    <span class="badge bg-light text-secondary border px-2">{{ $rm->letter_grade ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace fw-bold text-info">
                                {{ is_numeric($rm->cgpa_perc) ? number_format((float) $rm->cgpa_perc, 2) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No marks records found in the database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Styles --}}
<style>
.cdoe-marks-upload-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Hero card */
.marks-hero-card {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 28px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    position: relative;
    overflow: hidden;
}
.marks-hero-card::before {
    content: '';
    position: absolute;
    top: -40px;
    right: -40px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%);
    pointer-events: none;
}
.marks-hero-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(99, 102, 241, 0.15);
    color: #818cf8;
    padding: 5px 12px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 12px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.marks-hero-title {
    font-size: 26px;
    font-weight: 700;
    color: #f8fafc;
    margin-bottom: 8px;
}
.marks-hero-title .highlight {
    background: linear-gradient(135deg, #6366f1, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.marks-hero-desc {
    color: #94a3b8;
    font-size: 13.5px;
    max-width: 720px;
    margin-bottom: 20px;
    line-height: 1.55;
}
.marks-hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.marks-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}
.marks-btn.outline {
    background: rgba(255, 255, 255, 0.05);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.marks-btn.outline:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.3);
}
.marks-btn.accent {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    border: none;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.marks-btn.accent:hover {
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.45);
    transform: translateY(-1px);
    color: #fff;
}
.marks-status-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.25);
    color: #34d399;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
}
.status-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

/* Stat Cards */
.marks-stat-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.marks-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}
.marks-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.marks-stat-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    display: block;
    margin-bottom: 2px;
}
.marks-stat-val {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0;
}

/* Panels */
.marks-panel-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 14px;
    overflow: hidden;
}
.marks-panel-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.marks-panel-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary, #0f172a);
}
.marks-panel-body {
    padding: 24px;
}

/* Dropzone */
.dropzone-box {
    border: 2px dashed rgba(99, 102, 241, 0.35);
    background: rgba(99, 102, 241, 0.02);
    border-radius: 12px;
    padding: 36px 20px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.dropzone-box:hover, .dropzone-box.dragover {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.06);
}
.dropzone-icon {
    font-size: 38px;
    color: #6366f1;
    margin-bottom: 10px;
}
.dropzone-text {
    font-weight: 600;
    color: var(--text-primary, #0f172a);
}
.dropzone-hint {
    font-size: 12px;
    color: #94a3b8;
}

/* Guide Table */
.marks-guide-table th {
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 8px;
}
.marks-guide-table td {
    font-size: 13px;
    padding: 7px 4px;
    border-bottom: 1px dashed rgba(0, 0, 0, 0.05);
}

/* Data Table */
.marks-data-table th {
    background: rgba(0, 0, 0, 0.02);
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}
.marks-data-table td {
    padding: 12px 16px;
    font-size: 13px;
}
</style>

{{-- Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('marksFileInput');
    const dropzone = document.getElementById('dropzoneArea');
    const fileInfo = document.getElementById('selectedFileInfo');
    const fileNameText = document.getElementById('fileNameText');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileNameText.textContent = this.files[0].name + ' (' + (this.files[0].size / 1024).toFixed(1) + ' KB)';
                fileInfo.classList.remove('d-none');
            }
        });
    }

    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', function(e) {
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileNameText.textContent = fileInput.files[0].name + ' (' + (fileInput.files[0].size / 1024).toFixed(1) + ' KB)';
                fileInfo.classList.remove('d-none');
            }
        });
    }
});
</script>
@endsection
