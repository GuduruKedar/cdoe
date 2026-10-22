@extends('layouts.app')

@section('page_title', 'Admission Data Upload')

@section('content')
<div class="cdoe-adm-upload-container">

    {{-- ================= 1. HERO HEADER ================= --}}
    <div class="adm-hero-card mb-4">
        <div class="adm-hero-content">
            <div class="adm-hero-badge">
                <i class="bi bi-person-plus-fill me-1"></i> Admissions • Institutional Student Enrollment
            </div>
            <h1 class="adm-hero-title">
                Admission Data <span class="highlight">Upload</span>
            </h1>
            <p class="adm-hero-desc">
                Bulk import new student admissions, register numbers, personal emails, regulations, and academic batches into the institutional admissions repository. Automatically detects duplicate application numbers and updates existing records.
            </p>
            <div class="adm-hero-actions">
                <a href="{{ route('admissions.datadownload') }}" class="adm-btn outline">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i>
                    <span>Admission Data Download / Directory</span>
                </a>
                <a href="{{ route('admissions.dataupload.sample') }}" class="adm-btn accent">
                    <i class="bi bi-download me-1"></i>
                    <span>Download Sample CSV Template</span>
                </a>
            </div>
        </div>
        <div class="adm-hero-aside">
            <div class="adm-status-pill">
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
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="adm-stat-meta">
                    <span class="adm-stat-label">Total Registered</span>
                    <h3 class="adm-stat-val">{{ number_format((int)$stats['total_students']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="adm-stat-meta">
                    <span class="adm-stat-label">Admitted Students</span>
                    <h3 class="adm-stat-val">{{ number_format((int)$stats['active_students']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background: rgba(6, 182, 212, 0.12); color: #06b6d4;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="adm-stat-meta">
                    <span class="adm-stat-label">Academic Programs</span>
                    <h3 class="adm-stat-val">{{ number_format((int)$stats['total_programs']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div class="adm-stat-meta">
                    <span class="adm-stat-label">Enrolled Batches</span>
                    <h3 class="adm-stat-val">{{ number_format((int)$stats['total_batches']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= 3. UPLOAD INTERFACE & FIELD GUIDE ================= --}}
    <div class="row g-4 mb-4">
        {{-- Upload Form --}}
        <div class="col-lg-7 col-12">
            <div class="adm-panel-card h-100">
                <div class="adm-panel-header">
                    <h5 class="adm-panel-title">
                        <i class="bi bi-cloud-arrow-up-fill me-2 text-primary"></i> 1. Select & Upload Admission CSV File
                    </h5>
                    <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill">
                        CSV Format Supported
                    </span>
                </div>
                <div class="adm-panel-body">
                    <form method="POST" action="{{ route('admissions.dataupload.post') }}" enctype="multipart/form-data" id="admDataUploadForm">
                        @csrf

                        {{-- Default Batch Code (Optional) --}}
                        <div class="mb-3">
                            <label class="form-label adm-form-label">
                                <i class="bi bi-tags me-1"></i> Default Batch Code (Optional)
                            </label>
                            <input type="text" name="adm_batch_code_default" class="form-control adm-input" placeholder="e.g. BATCH-2026 (used if column is empty in CSV)">
                            <div class="form-text small" style="color: #94a3b8;">
                                If your CSV already specifies the <code>adm_batch_code</code> column per student, that row value takes precedence.
                            </div>
                        </div>

                        {{-- Drag & Drop Area --}}
                        <div class="dropzone-box" id="dropzoneAdmArea">
                            <input type="file" name="admission_file" id="admFileInput" accept=".csv,text/csv" required class="d-none">
                            <div class="dropzone-inner text-center" onclick="document.getElementById('admFileInput').click()">
                                <div class="dropzone-icon">
                                    <i class="bi bi-cloud-upload-fill"></i>
                                </div>
                                <h6 class="dropzone-text mb-1">Click to browse or drag and drop your applicant CSV here</h6>
                                <p class="dropzone-hint mb-0">Standard CSV files up to 20 MB accepted</p>
                                <div id="selectedAdmFileInfo" class="mt-3 d-none">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill font-monospace" id="admFileNameBadge">
                                        <i class="bi bi-file-earmark-check-fill me-1"></i> <span id="admFileNameText"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex align-items-center justify-content-between mt-4 gap-2 flex-wrap">
                            <a href="{{ route('admissions.dataupload.sample') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                                <i class="bi bi-download me-1"></i> Sample Template
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                                <i class="bi bi-cloud-check-fill"></i>
                                <span>Process & Upload Admissions</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Guide & Requirements --}}
        <div class="col-lg-5 col-12">
            <div class="adm-panel-card h-100">
                <div class="adm-panel-header">
                    <h5 class="adm-panel-title">
                        <i class="bi bi-info-circle-fill me-2 text-info"></i> 2. Template Column Reference
                    </h5>
                    <a href="{{ route('admissions.dataupload.sample') }}" class="small text-decoration-none text-primary fw-semibold">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Sample CSV
                    </a>
                </div>
                <div class="adm-panel-body">
                    <p class="small text-muted mb-3">
                        The CSV file must contain the following required headers. Additional columns are mapped automatically:
                    </p>
                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-borderless adm-guide-table">
                            <thead>
                                <tr>
                                    <th>Header</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>application_no</code></td>
                                    <td>String (Unique)</td>
                                    <td><span class="badge bg-danger-subtle text-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>student_name</code></td>
                                    <td>String</td>
                                    <td><span class="badge bg-danger-subtle text-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>register_no</code></td>
                                    <td>String</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>personal_email</code></td>
                                    <td>Email</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>mobile</code></td>
                                    <td>Phone Number</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>progid</code></td>
                                    <td>Integer (1=MBA, 2=MCA..)</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>gender</code></td>
                                    <td>Male / Female</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                                <tr>
                                    <td><code>adm_batch_code</code></td>
                                    <td>Batch Code</td>
                                    <td><span class="badge bg-info-subtle text-info">Optional</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= 4. RECENT ADMISSION RECORDS PREVIEW ================= --}}
    <div class="adm-panel-card">
        <div class="adm-panel-header">
            <div>
                <h5 class="adm-panel-title mb-1">
                    <i class="bi bi-clock-history me-2 text-warning"></i> Recent Student Admissions Preview
                </h5>
                <span class="text-muted small">Showing the latest verified student registrations in the database</span>
            </div>
            <a href="{{ route('admissions.datadownload') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-arrow-up-right me-1"></i> Full Directory
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 adm-data-table">
                <thead>
                    <tr>
                        <th>Application No</th>
                        <th>Register No</th>
                        <th>Student Name</th>
                        <th>Program</th>
                        <th>Batch Code</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentStudents as $rs)
                        <tr>
                            <td>
                                <span class="fw-bold font-monospace text-primary">{{ $rs->application_no }}</span>
                            </td>
                            <td>
                                <span class="font-monospace fw-semibold">{{ $rs->register_no ?? '—' }}</span>
                            </td>
                            <td>{{ $rs->student_name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $rs->program_short_name ?? 'CDOE' }}</span>
                            </td>
                            <td>{{ $rs->adm_batch_code ?? '—' }}</td>
                            <td class="font-monospace small">{{ $rs->mobile ?? '—' }}</td>
                            <td class="small text-muted">{{ $rs->personal_email ?? '—' }}</td>
                            <td class="text-center">
                                @if($rs->std_status === 'Admitted')
                                    <span class="badge bg-success-subtle text-success fw-bold px-2">Admitted</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2">{{ $rs->std_status ?? 'Registered' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No student registrations found in the database.
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
.cdoe-adm-upload-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Hero card */
.adm-hero-card {
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
.adm-hero-card::before {
    content: '';
    position: absolute;
    top: -40px;
    right: -40px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%);
    pointer-events: none;
}
.adm-hero-badge {
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
.adm-hero-title {
    font-size: 26px;
    font-weight: 700;
    color: #f8fafc;
    margin-bottom: 8px;
}
.adm-hero-title .highlight {
    background: linear-gradient(135deg, #6366f1, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.adm-hero-desc {
    color: #94a3b8;
    font-size: 13.5px;
    max-width: 720px;
    margin-bottom: 20px;
    line-height: 1.55;
}
.adm-hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.adm-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}
.adm-btn.outline {
    background: rgba(255, 255, 255, 0.05);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.adm-btn.outline:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.3);
}
.adm-btn.accent {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    border: none;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.adm-btn.accent:hover {
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.45);
    transform: translateY(-1px);
    color: #fff;
}
.adm-status-pill {
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
.adm-stat-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.adm-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}
.adm-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.adm-stat-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    display: block;
    margin-bottom: 2px;
}
.adm-stat-val {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    margin: 0;
}

/* Panels */
.adm-panel-card {
    background: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 14px;
    overflow: hidden;
}
.adm-panel-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.adm-panel-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary, #0f172a);
}
.adm-panel-body {
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
.adm-guide-table th {
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 8px;
}
.adm-guide-table td {
    font-size: 13px;
    padding: 7px 4px;
    border-bottom: 1px dashed rgba(0, 0, 0, 0.05);
}

/* Data Table */
.adm-data-table th {
    background: rgba(0, 0, 0, 0.02);
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}
.adm-data-table td {
    padding: 12px 16px;
    font-size: 13px;
}
</style>

{{-- Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('admFileInput');
    const dropzone = document.getElementById('dropzoneAdmArea');
    const fileInfo = document.getElementById('selectedAdmFileInfo');
    const fileNameText = document.getElementById('admFileNameText');

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
