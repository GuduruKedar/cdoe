@extends('layouts.app')

@section('page_title', 'Course Registration Upload')

@section('content')
<div class="cdoe-coursereg-upload-container">

    {{-- ================= 1. HERO HEADER ================= --}}
    <div class="coursereg-hero-card mb-4">
        <div class="coursereg-hero-content">
            <div class="coursereg-hero-badge">
                <i class="bi bi-mortarboard-fill me-1"></i> Academics • Curriculum & Student Enrollment
            </div>
            <h1 class="coursereg-hero-title">
                Course Registration <span class="highlight">Upload</span>
            </h1>
            <p class="coursereg-hero-desc">
                Bulk upload student course enrollments, semester mappings, classroom assignments, and regulation details using standard CSV format. Prevents duplicate course registrations and maintains complete academic history.
            </p>
            <div class="coursereg-hero-actions">
                <a href="{{ route('academics.coursereg') }}" class="coursereg-btn outline">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>
                    <span>Course Registration Download / Directory</span>
                </a>
                <a href="{{ route('academics.coursereg.upload.sample') }}" class="coursereg-btn accent">
                    <i class="bi bi-download me-1"></i>
                    <span>Download Sample CSV Template</span>
                </a>
            </div>
        </div>
        <div class="coursereg-hero-aside">
            <div class="coursereg-status-pill">
                <span class="status-dot"></span>
                <span>Portal Live • Verified</span>
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
            <div class="coursereg-stat-card">
                <div class="coursereg-stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                    <i class="bi bi-card-checklist"></i>
                </div>
                <div class="coursereg-stat-meta">
                    <span class="coursereg-stat-label">Total Registrations</span>
                    <h3 class="coursereg-stat-val">{{ number_format((int)$stats['total_registrations']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="coursereg-stat-card">
                <div class="coursereg-stat-icon" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div class="coursereg-stat-meta">
                    <span class="coursereg-stat-label">Enrolled Students</span>
                    <h3 class="coursereg-stat-val">{{ number_format((int)$stats['total_students']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="coursereg-stat-card">
                <div class="coursereg-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                    <i class="bi bi-book-half"></i>
                </div>
                <div class="coursereg-stat-meta">
                    <span class="coursereg-stat-label">Available Courses</span>
                    <h3 class="coursereg-stat-val">{{ number_format((int)$stats['distinct_subjects']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="coursereg-stat-card">
                <div class="coursereg-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                    <i class="bi bi-calendar3-range-fill"></i>
                </div>
                <div class="coursereg-stat-meta">
                    <span class="coursereg-stat-label">Active Semesters</span>
                    <h3 class="coursereg-stat-val">{{ number_format((int)$stats['distinct_semesters']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= 3. UPLOAD INTERFACE & FIELD GUIDE ================= --}}
    <div class="row g-4 mb-4">
        {{-- Upload Form --}}
        <div class="col-lg-7 col-12">
            <div class="coursereg-panel-card h-100">
                <div class="coursereg-panel-header">
                    <h5 class="coursereg-panel-title">
                        <i class="bi bi-cloud-arrow-up-fill me-2 text-primary"></i> 1. Select & Upload Registrations CSV
                    </h5>
                    <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill">
                        CSV Format Supported
                    </span>
                </div>
                <div class="coursereg-panel-body">
                    <form method="POST" action="{{ route('academics.coursereg.upload.post') }}" enctype="multipart/form-data" id="courseRegUploadForm">
                        @csrf

                        {{-- Semester Code Default (Optional) --}}
                        <div class="mb-3">
                            <label class="form-label coursereg-form-label">
                                <i class="bi bi-calendar3 me-1"></i> Semester Code (Batch Default)
                            </label>
                            <input type="text" name="semcode_default" class="form-control coursereg-input" placeholder="e.g. SEM-2025-1 (used if column is empty in CSV)">
                            <div class="form-text small" style="color: #94a3b8;">
                                If your CSV already specifies the <code>semcode</code> column per student, that row value takes precedence.
                            </div>
                        </div>

                        {{-- Drag & Drop Area --}}
                        <div class="dropzone-box" id="dropzoneCourseArea">
                            <input type="file" name="registration_file" id="courseFileInput" accept=".csv,text/csv" required class="d-none">
                            <div class="dropzone-inner text-center" onclick="document.getElementById('courseFileInput').click()">
                                <div class="dropzone-icon">
                                    <i class="bi bi-cloud-upload-fill"></i>
                                </div>
                                <h6 class="dropzone-text mb-1">Click to browse or drag and drop your course registration CSV here</h6>
                                <p class="dropzone-hint mb-0">Standard CSV files up to 20 MB accepted</p>
                                <div id="selectedCourseFileInfo" class="mt-3 d-none">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill font-monospace" id="courseFileNameBadge">
                                        <i class="bi bi-file-earmark-check-fill me-1"></i> <span id="courseFileNameText"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex align-items-center justify-content-between mt-4 gap-2 flex-wrap">
                            <a href="{{ route('academics.coursereg.upload.sample') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                                <i class="bi bi-download me-1"></i> Sample Template
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                                <i class="bi bi-cloud-check-fill"></i>
                                <span>Process & Upload Registrations</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Guide & Requirements --}}
        <div class="col-lg-5 col-12">
            <div class="coursereg-panel-card h-100">
                <div class="coursereg-panel-header">
                    <h5 class="coursereg-panel-title">
                        <i class="bi bi-info-circle-fill me-2 text-info"></i> 2. Template Column Reference
                    </h5>
                    <a href="{{ route('academics.coursereg.upload.sample') }}" class="small text-decoration-none text-primary fw-semibold">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Sample CSV
                    </a>
                </div>
                <div class="coursereg-panel-body">
                    <p class="small text-muted mb-3">
                        The CSV file must contain the following columns. Required fields ensure valid academic mapping:
                    </p>
                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-borderless coursereg-guide-table">
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
                                    <td><code>semcode</code></td>
                                    <td>String</td>
                                    <td><span class="badge bg-danger-subtle text-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>s_year</code></td>
                                    <td>Integer (1–4)</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>s_sem</code></td>
                                    <td>Integer (1–8)</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>section_no</code></td>
                                    <td>String</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>classno</code></td>
                                    <td>String</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>reg_type</code></td>
                                    <td>R (Regular) / S (Supple)</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= 4. RECENT COURSE REGISTRATIONS PREVIEW ================= --}}
    <div class="coursereg-panel-card">
        <div class="coursereg-panel-header">
            <div>
                <h5 class="coursereg-panel-title mb-1">
                    <i class="bi bi-clock-history me-2 text-warning"></i> Recent Course Registrations Preview
                </h5>
                <span class="text-muted small">Showing the latest verified course enrollments in the database</span>
            </div>
            <a href="{{ route('academics.coursereg') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-arrow-up-right me-1"></i> Full Directory
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 coursereg-data-table">
                <thead>
                    <tr>
                        <th>Register No</th>
                        <th>Student Name</th>
                        <th>Semester Code</th>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th class="text-center">Year / Sem</th>
                        <th class="text-center">Section</th>
                        <th class="text-center">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRegistrations as $rr)
                        <tr>
                            <td>
                                <span class="fw-bold font-monospace text-primary">{{ $rr->regno }}</span>
                            </td>
                            <td>{{ $rr->student_name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $rr->semcode }}</span>
                            </td>
                            <td><span class="font-monospace fw-semibold">{{ $rr->subcode }}</span></td>
                            <td>{{ $rr->subname ?? '—' }}</td>
                            <td class="text-center font-monospace">
                                Yr {{ $rr->s_year ?? '-' }} / Sem {{ $rr->s_sem ?? '-' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info fw-bold">{{ $rr->section_no ?? 'A' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary">{{ $rr->reg_type ?? 'R' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No course registrations found in the database.
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
.cdoe-coursereg-upload-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Hero card */
.coursereg-hero-card {
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
.coursereg-hero-card::before {
    content: '';
    position: absolute;
    top: -40px;
    right: -40px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, transparent 70%);
    pointer-events: none;
}
.coursereg-hero-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    padding: 5px 12px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 12px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.coursereg-hero-title {
    font-size: 26px;
    font-weight: 700;
    color: #f8fafc;
    margin-bottom: 8px;
}
.coursereg-hero-title .highlight {
    background: linear-gradient(135deg, #10b981, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.coursereg-hero-desc {
    color: #94a3b8;
    font-size: 13.5px;
    max-width: 720px;
    margin-bottom: 20px;
    line-height: 1.55;
}
.coursereg-hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.coursereg-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}
.coursereg-btn.outline {
    background: rgba(255, 255, 255, 0.05);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.coursereg-btn.outline:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.3);
}
.coursereg-btn.accent {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}
.coursereg-btn.accent:hover {
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.45);
    transform: translateY(-1px);
    color: #fff;
}
.coursereg-status-pill {
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

/* Stat Cards */
.coursereg-stat-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.coursereg-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}
.coursereg-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.coursereg-stat-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    display: block;
    margin-bottom: 2px;
}
.coursereg-stat-val {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0;
}

/* Panels */
.coursereg-panel-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 14px;
    overflow: hidden;
}
.coursereg-panel-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.coursereg-panel-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary, #0f172a);
}
.coursereg-panel-body {
    padding: 24px;
}

/* Dropzone */
.dropzone-box {
    border: 2px dashed rgba(16, 185, 129, 0.35);
    background: rgba(16, 185, 129, 0.02);
    border-radius: 12px;
    padding: 36px 20px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.dropzone-box:hover, .dropzone-box.dragover {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.06);
}
.dropzone-icon {
    font-size: 38px;
    color: #10b981;
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
.coursereg-guide-table th {
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 8px;
}
.coursereg-guide-table td {
    font-size: 13px;
    padding: 7px 4px;
    border-bottom: 1px dashed rgba(0, 0, 0, 0.05);
}

/* Data Table */
.coursereg-data-table th {
    background: rgba(0, 0, 0, 0.02);
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}
.coursereg-data-table td {
    padding: 12px 16px;
    font-size: 13px;
}
</style>

{{-- Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('courseFileInput');
    const dropzone = document.getElementById('dropzoneCourseArea');
    const fileInfo = document.getElementById('selectedCourseFileInfo');
    const fileNameText = document.getElementById('courseFileNameText');

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
