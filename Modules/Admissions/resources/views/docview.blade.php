@extends('layouts.app')

@section('page_title', 'Student Document Verification & View')

@section('content')
<div class="cdoe-doc-view-container">

    {{-- ================= 1. FILTER TOOLBAR (Matches Image 2 & 3) ================= --}}
    <div class="filter-toolbar-box mb-3">
        <form method="GET" action="{{ route('admissions.docview') }}" id="filterForm">
            <div class="row g-2 align-items-end">

                {{-- Department / Program --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-mortarboard-fill me-1"></i> Department / Program
                    </label>
                    <select name="program" class="form-select filter-select-blue">
                        <option value="all">Select Program ▼</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->program_id }}" {{ (string)$program === (string)$p->program_id ? 'selected' : '' }}>
                                {{ $p->program_code }} - {{ $p->program_long_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Batch --}}
                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-calendar-event-fill me-1"></i> Batch
                    </label>
                    <select name="batch" class="form-select filter-select-blue">
                        <option value="all">Select Batch ▼</option>
                        @foreach($batches as $b)
                            <option value="{{ $b }}" {{ $batch === $b ? 'selected' : '' }}>
                                {{ $b }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status (Image 3 wireframe 3rd dropdown) --}}
                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-check2-circle me-1"></i> Status
                    </label>
                    <select name="status" class="form-select filter-select-blue">
                        <option value="all">Select Status ▼</option>
                        @if(isset($statuses))
                            @foreach($statuses as $st)
                                <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>
                                    {{ ucfirst($st) }}
                                </option>
                            @endforeach
                        @else
                            <option value="Active" {{ $status === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ $status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        @endif
                    </select>
                </div>

                {{-- Search Input --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-search me-1"></i> Search Reg No / Name
                    </label>
                    <input type="text"
                           name="q"
                           class="form-control filter-input-search"
                           placeholder="Enter register no or name..."
                           value="{{ $q ?? '' }}">
                </div>

                {{-- Action Buttons Row (Matches Image 2 & 3) --}}
                <div class="col-xl-4 col-lg-12 col-md-12 text-end d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <button type="submit" class="btn btn-action btn-apply">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('admissions.docview') }}" class="btn btn-action btn-clear">
                        <i class="bi bi-trash3-fill me-1"></i> Clear All
                    </a>
                    <button type="button" class="btn btn-action btn-print" onclick="window.print()">
                        <i class="bi bi-printer-fill me-1"></i> Print
                    </button>
                    <a href="{{ route('admissions.docview.export', request()->all()) }}" class="btn btn-action btn-download">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <a href="{{ route('admissions.docupload') }}" class="btn btn-action btn-back">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ================= 2. ACTIVE FILTERS BANNER (Matches Image 3) ================= --}}
    <div class="active-filters-ribbon mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="ribbon-pin">📌</span>
            <span class="fw-semibold">
                Active Filters:
                @if((empty($program) || $program === 'all') && (empty($batch) || $batch === 'all') && (empty($status) || $status === 'all') && empty($q))
                    <span class="text-muted fw-normal">No filters applied (showing all records)</span>
                @else
                    <span class="text-primary fw-bold">
                        {{ $program && $program !== 'all' ? 'Program: ' . $program . ' | ' : '' }}
                        {{ $batch && $batch !== 'all' ? 'Batch: ' . $batch . ' | ' : '' }}
                        {{ $status && $status !== 'all' ? 'Status: ' . $status . ' | ' : '' }}
                        {{ $q ? 'Query: "' . $q . '"' : '' }}
                    </span>
                @endif
            </span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span>📊 <strong>Total Records:</strong> <span class="font-monospace fw-bold">{{ $totalRecords }}</span></span>
            <span>|</span>
            <span>🔍 <strong>Filtered Records:</strong> <span class="font-monospace fw-bold">{{ $filteredCount }}</span></span>
        </div>
    </div>

    {{-- ================= 3. DOCUMENT VERIFICATION MATRIX TABLE (Matches Image 3) ================= --}}
    <div class="table-card-container">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 matrix-table">
                <thead class="matrix-header">
                    <tr>
                        <th style="min-width: 50px;">S.No</th>
                        <th style="min-width: 110px;">Reg No</th>
                        <th style="min-width: 160px;">Student Name</th>
                        <th style="min-width: 80px;">Doc No</th>
                        <th style="min-width: 85px; text-align: center;">Photo</th>
                        <th style="min-width: 105px; text-align: center;">Aadhar</th>
                        <th style="min-width: 95px; text-align: center;">PAN</th>
                        <th style="min-width: 95px; text-align: center;">APAAR</th>
                        <th style="min-width: 100px; text-align: center;">Other ID</th>
                        <th style="min-width: 90px; text-align: center;">SSC</th>
                        <th style="min-width: 90px; text-align: center;">Inter</th>
                        <th style="min-width: 90px; text-align: center;">Diploma</th>
                        <th style="min-width: 90px; text-align: center;">UG</th>
                        <th style="min-width: 90px; text-align: center;">PG</th>
                        <th style="min-width: 90px; text-align: center;">PhD</th>
                        <th style="min-width: 85px; text-align: center;">ALL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $idx => $s)
                        @php
                            $docs = $studentDocs[$s->application_no] ?? [];
                            $docNo = $docs['_docno'] ?? '—';
                        @endphp
                        <tr>
                            <td class="text-center font-monospace fw-semibold">{{ $students->firstItem() + $idx }}</td>
                            <td>
                                <a href="{{ route('admissions.docupload', ['regno' => $s->register_no]) }}" class="regno-link" title="Open in Document Upload">
                                    {{ $s->register_no }}
                                </a>
                                <div class="small text-muted">{{ $s->program_code }}</div>
                            </td>
                            <td>
                                <div class="fw-bold student-name-text text-truncate" style="max-width: 170px;" title="{{ $s->student_name }}">
                                    {{ $s->student_name }}
                                </div>
                                <div class="small text-muted font-monospace">{{ $s->adm_batch_code }}</div>
                            </td>
                            <td class="font-monospace small text-muted text-center">{{ $docNo }}</td>

                            {{-- PHOTO column --}}
                            <td class="text-center">
                                @if(isset($docs['photo']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <img src="{{ $docs['photo']->file_url }}"
                                             class="avatar-circle-sm"
                                             alt="Photo"
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($s->student_name) }}&background=0d6efd&color=fff&size=64';"
                                             onclick="openDocPreview('{{ $docs['photo']->file_url }}', '{{ addslashes($s->student_name) }} - Photo', true)">
                                        <a href="{{ route('admissions.docview.download', $docs['photo']->doc_id) }}" class="btn-doc-download" title="Download Photo">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- AADHAR column --}}
                            <td class="text-center">
                                @if(isset($docs['aadhar']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['aadhar']->file_url }}', '{{ addslashes($s->student_name) }} - Aadhar', false)">
                                            <i class="bi bi-eye"></i> aadhar
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['aadhar']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- PAN column --}}
                            <td class="text-center">
                                @if(isset($docs['pan']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['pan']->file_url }}', '{{ addslashes($s->student_name) }} - PAN', false)">
                                            <i class="bi bi-eye"></i> pan
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['pan']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- APAAR column --}}
                            <td class="text-center">
                                @if(isset($docs['apaar']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['apaar']->file_url }}', '{{ addslashes($s->student_name) }} - APAAR', false)">
                                            <i class="bi bi-eye"></i> apaar
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['apaar']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- OTHER ID column --}}
                            <td class="text-center">
                                @if(isset($docs['other_id']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['other_id']->file_url }}', '{{ addslashes($s->student_name) }} - Other ID', false)">
                                            <i class="bi bi-eye"></i> OtherID
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['other_id']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- SSC column --}}
                            <td class="text-center">
                                @if(isset($docs['ssc']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['ssc']->file_url }}', '{{ addslashes($s->student_name) }} - SSC', false)">
                                            <i class="bi bi-eye"></i> SSC
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['ssc']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- INTER column --}}
                            <td class="text-center">
                                @if(isset($docs['inter']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['inter']->file_url }}', '{{ addslashes($s->student_name) }} - Inter', false)">
                                            <i class="bi bi-eye"></i> Inter
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['inter']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- DIPLOMA column --}}
                            <td class="text-center">
                                @if(isset($docs['diploma']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['diploma']->file_url }}', '{{ addslashes($s->student_name) }} - Diploma', false)">
                                            <i class="bi bi-eye"></i> Diploma
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['diploma']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- UG column --}}
                            <td class="text-center">
                                @if(isset($docs['ug']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['ug']->file_url }}', '{{ addslashes($s->student_name) }} - UG Degree', false)">
                                            <i class="bi bi-eye"></i> UG
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['ug']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- PG column --}}
                            <td class="text-center">
                                @if(isset($docs['pg']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['pg']->file_url }}', '{{ addslashes($s->student_name) }} - PG Degree', false)">
                                            <i class="bi bi-eye"></i> PG
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['pg']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- PHD column --}}
                            <td class="text-center">
                                @if(isset($docs['phd']))
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn-doc-view" onclick="openDocPreview('{{ $docs['phd']->file_url }}', '{{ addslashes($s->student_name) }} - PhD', false)">
                                            <i class="bi bi-eye"></i> PhD
                                        </button>
                                        <a href="{{ route('admissions.docview.download', $docs['phd']->doc_id) }}" class="btn-doc-download" title="Download">
                                            <i class="bi bi-arrow-down-short"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="na-badge">NA</span>
                                @endif
                            </td>

                            {{-- ALL column --}}
                            <td class="text-center">
                                <a href="{{ route('admissions.docupload', ['regno' => $s->register_no]) }}" class="btn-doc-upload-all" title="Upload / Manage all documents for this student">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2 text-muted"></i>
                                No student records found matching the applied filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination using Bootstrap 5 template --}}
        <div class="table-pagination-footer">
            <span class="small">
                Showing <strong class="text-primary">{{ $students->firstItem() ?? 0 }}</strong> to <strong class="text-primary">{{ $students->lastItem() ?? 0 }}</strong> of <strong class="text-primary">{{ $students->total() }}</strong> students
            </span>
            <div class="pagination-wrapper">
                {{ $students->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

{{-- ================= 4. DOCUMENT PREVIEW MODAL ================= --}}
<div class="modal fade" id="matrixPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg cdoe-modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="matrixPreviewTitle">Document Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3" id="matrixPreviewBody"></div>
            <div class="modal-footer border-0 pt-0">
                <a href="#" id="matrixDownloadBtn" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openDocPreview(url, title, isImage) {
        document.getElementById('matrixPreviewTitle').textContent = title || 'Document Preview';
        document.getElementById('matrixDownloadBtn').href = url;
        const body = document.getElementById('matrixPreviewBody');

        if (isImage) {
            body.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center p-2">
                    <img src="${url}" class="img-fluid rounded shadow-sm" style="max-height: 65vh; max-width: 100%; object-fit: contain;" alt="Preview"
                         onerror="this.parentElement.innerHTML='<div class=\\'alert alert-warning py-4 px-3 mb-0 text-center\\'><i class=\\'bi bi-image fs-1 text-warning d-block mb-2\\'></i><strong>Image Preview Not Available</strong><p class=\\'text-muted small mb-3\\'>The image file could not be rendered or was not found in storage.</p><a href=\\'${url}\\' class=\\'btn btn-sm btn-outline-primary\\' target=\\'_blank\\'><i class=\\'bi bi-box-arrow-up-right me-1\\'></i>Open Direct Link</a></div>';">
                </div>
            `;
        } else {
            body.innerHTML = `
                <div style="height: 70vh; width: 100%;">
                    <iframe src="${url}" style="width: 100%; height: 100%; border: none; border-radius: 8px;"></iframe>
                </div>
            `;
        }

        const modal = new bootstrap.Modal(document.getElementById('matrixPreviewModal'));
        modal.show();
    }
</script>

<style>
    /* ================= EXACT STYLING MATCHING IMAGE 3 (DARK & LIGHT THEME ADAPTIVE) ================= */
    .filter-toolbar-box {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-card) !important;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: var(--card-shadow, 0 4px 18px rgba(0, 0, 0, 0.08)) !important;
        color: var(--text-primary);
    }
    .filter-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-primary) !important;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
    }
    .filter-select-blue {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        font-weight: 600;
        font-size: 13px;
        border-radius: 6px;
        border: 1px solid #0b5ed7;
        padding: 6px 30px 6px 12px;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 10px center !important;
        background-size: 12px !important;
        cursor: pointer;
    }
    .filter-select-blue option {
        background-color: var(--bg-surface, #ffffff);
        color: var(--text-primary, #0f172a);
    }
    .filter-input-search {
        background-color: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-subtle) !important;
        border-radius: 6px;
        font-size: 13px;
        padding: 6px 12px;
    }
    .filter-input-search::placeholder {
        color: var(--text-muted);
        opacity: 0.8;
    }
    .filter-input-search:focus {
        background-color: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2) !important;
    }
    .btn-action {
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 16px;
        color: #ffffff !important;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        transition: transform 0.15s ease, filter 0.15s ease;
        text-decoration: none;
    }
    .btn-action:hover {
        transform: translateY(-1px);
        filter: brightness(1.08);
    }
    .btn-apply    { background-color: #0d6efd; }
    .btn-clear    { background-color: #dc3545; }
    .btn-print    { background-color: #198754; }
    .btn-download { background-color: #fd7e14; }
    .btn-back     { background-color: #6c757d; }

    .active-filters-ribbon {
        background-color: var(--bg-surface) !important;
        border: 1px solid var(--border-subtle) !important;
        border-radius: 8px;
        padding: 10px 18px;
        font-size: 12.5px;
        color: var(--text-primary) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .table-card-container {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-card) !important;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--card-shadow, 0 4px 18px rgba(0, 0, 0, 0.08)) !important;
    }
    .matrix-table {
        color: var(--text-primary);
        border-color: var(--border-subtle) !important;
        margin-bottom: 0;
    }
    .matrix-header th {
        background-color: #e83e8c !important;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 700;
        padding: 11px 8px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-color: #d63384 !important;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
    }
    .matrix-table tbody td {
        font-size: 12.5px;
        padding: 9px 8px;
        border-color: var(--border-subtle) !important;
        vertical-align: middle;
        color: var(--text-primary);
        background: transparent !important;
    }
    .matrix-table tbody tr:hover td {
        background-color: var(--bg-hover-row) !important;
    }

    .regno-link {
        color: #38bdf8 !important;
        font-family: monospace;
        font-weight: 700;
        text-decoration: none;
    }
    .regno-link:hover {
        text-decoration: underline;
        color: #0ea5e9 !important;
    }

    .student-name-text {
        color: var(--text-primary);
    }

    /* Buttons inside table cells matching Image 3 */
    .avatar-circle-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #0d6efd;
        cursor: pointer;
        transition: transform 0.15s ease;
    }
    .avatar-circle-sm:hover {
        transform: scale(1.15);
    }
    .btn-doc-view {
        background-color: var(--bg-surface);
        color: var(--text-primary);
        border: 1px solid var(--border-subtle);
        border-radius: 14px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: all 0.15s ease;
    }
    .btn-doc-view:hover {
        background-color: var(--bg-elevated, #1e293b);
        border-color: #0d6efd;
        color: #0d6efd;
        transform: translateY(-1px);
    }
    .btn-doc-download {
        background-color: #0d6efd;
        color: #ffffff !important;
        border-radius: 4px;
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        text-decoration: none;
        transition: transform 0.15s ease, background-color 0.15s ease;
    }
    .btn-doc-download:hover {
        background-color: #0b5ed7;
        transform: scale(1.1);
    }
    .btn-doc-upload-all {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: #ffffff !important;
        border-radius: 6px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 1px 3px rgba(13, 110, 253, 0.25);
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .btn-doc-upload-all:hover {
        background: #0b5ed7;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(13, 110, 253, 0.4);
    }
    .na-badge {
        color: var(--text-muted);
        font-size: 11.5px;
        font-weight: 600;
        opacity: 0.7;
    }

    .table-pagination-footer {
        padding: 12px 20px;
        border-top: 1px solid var(--border-subtle);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-card) !important;
        color: var(--text-muted);
        flex-wrap: wrap;
        gap: 12px;
    }
    .pagination-wrapper .pagination {
        margin-bottom: 0;
    }
    .pagination-wrapper .page-link {
        background-color: var(--bg-surface);
        color: var(--text-primary);
        border-color: var(--border-subtle);
        font-size: 12.5px;
        padding: 5px 12px;
    }
    .pagination-wrapper .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #ffffff;
    }
    .pagination-wrapper .page-item.disabled .page-link {
        background-color: var(--bg-card);
        color: var(--text-muted);
        border-color: var(--border-subtle);
    }
    .pagination svg, nav svg {
        width: 14px !important;
        height: 14px !important;
        max-width: 14px !important;
        max-height: 14px !important;
        display: inline-block !important;
    }

    /* Modal styling */
    .cdoe-modal-content {
        background-color: var(--bg-card) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-card) !important;
        border-radius: 16px;
    }

    @media print {
        .filter-toolbar-box, .active-filters-ribbon, .table-pagination-footer, .topnav, .sidebar {
            display: none !important;
        }
    }
</style>
@endsection
