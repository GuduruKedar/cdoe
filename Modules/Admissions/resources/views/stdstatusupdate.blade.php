@extends('layouts.app')

@section('page_title', 'Student Status Update')

@section('content')
<div class="std-status-container">

    {{-- ================= ALERTS ================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" role="alert" style="background: rgba(16, 185, 129, 0.12); color: #059669; border-left: 4px solid #10b981 !important; border-radius: 10px;">
            <i class="bi bi-check-circle-fill fs-5 me-2 flex-shrink-0"></i>
            <div class="flex-grow-1 fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" role="alert" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; border-left: 4px solid #ef4444 !important; border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
            <div class="flex-grow-1 fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ================= 1. SEARCH BAR CARD ================= --}}
    <div class="search-card mb-3 p-3">
        <form action="{{ route('admissions.stdstatusupdate') }}" method="GET" id="statusLookupForm">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <label for="studentRegNoInput" class="form-label fw-bold mb-0 text-dark" style="font-size: 14px;">
                    <i class="bi bi-search me-1 text-primary"></i> Enter Student Reg No / Appl No
                </label>
                @if ($student)
                    <a href="{{ route('admissions.stdstatusupdate') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 py-1 px-2.5" style="font-size: 12.5px;">
                        <i class="bi bi-x-circle"></i> Clear Search
                    </a>
                @endif
            </div>

            <div class="input-group search-input-group position-relative">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-person-badge"></i>
                </span>
                <input
                    type="text"
                    name="regno"
                    id="studentRegNoInput"
                    class="form-control border-start-0 border-end-0 @if($studentNotFound) is-invalid @elseif($student) is-valid @endif"
                    placeholder="Enter Student Reg No / Appl No (e.g. 261CK01047, 261CK01026...)"
                    value="{{ $searchedRegNo }}"
                    required
                    autocomplete="off">
                <button class="btn btn-primary px-4 fw-semibold d-inline-flex align-items-center gap-1.5" type="submit" id="searchSubmitBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="searchSpinner"></span>
                    <i class="bi bi-search"></i>
                    <span>Search</span>
                </button>
            </div>
            <div class="autocomplete-dropdown shadow-lg rounded-3" id="autocompleteDropdown" style="display: none;"></div>
        </form>
    </div>

    {{-- Case: Student Not Found Alert --}}
    @if ($studentNotFound)
        <div class="alert alert-warning border border-warning-subtle shadow-sm mb-3 p-3 rounded-3 text-start">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-4 flex-shrink-0"></i>
                <div>
                    <span class="fw-bold text-dark">Student Not Found:</span>
                    <span class="text-muted small">No record matches register number or application number <strong>"{{ $searchedRegNo }}"</strong> in admissions database.</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Case: Student Found -> Display Profile & Status Update Card --}}
    @if ($student)
        @php
            $normalizedCurrent = \Modules\Admissions\Http\Controllers\StudentStatusUpdateController::normalizeStatus($student->std_status);
            $meta = $statuses[$normalizedCurrent] ?? [
                'label' => $normalizedCurrent,
                'badge_class' => 'badge-status-active',
                'icon' => 'bi-check-circle-fill',
            ];
        @endphp

        <div class="status-student-card mb-3 p-3 rounded-3 shadow-sm border">
            {{-- Header Line --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-2 mb-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold">
                        <i class="bi bi-patch-check-fill me-1"></i>Verified Student
                    </span>
                    <h5 class="fw-bold mb-0 text-dark">{{ $student->student_name }}</h5>
                </div>
                <div class="text-muted small">
                    Application No: <span class="font-monospace text-dark fw-semibold">{{ $student->application_no }}</span>
                </div>
            </div>

            {{-- 5 Student Information Columns (Directly from user's handwritten wireframe) --}}
            <div class="row g-2 p-2.5 rounded-2 mb-3 student-info-grid">
                {{-- 1. Regno --}}
                <div class="col-md-2 col-sm-6">
                    <div class="info-field-box">
                        <span class="field-label">Regno</span>
                        <div class="field-value font-monospace fw-bold text-primary">
                            {{ $student->register_no ?: $student->application_no }}
                        </div>
                    </div>
                </div>

                {{-- 2. Name --}}
                <div class="col-md-3 col-sm-6">
                    <div class="info-field-box">
                        <span class="field-label">Name</span>
                        <div class="field-value fw-semibold text-dark text-truncate" title="{{ $student->student_name }}">
                            {{ $student->student_name }}
                        </div>
                    </div>
                </div>

                {{-- 3. phno --}}
                <div class="col-md-2 col-sm-6">
                    <div class="info-field-box">
                        <span class="field-label">phno</span>
                        <div class="field-value text-dark fw-medium">
                            {{ $student->mobile ?: '—' }}
                        </div>
                    </div>
                </div>

                {{-- 4. gmail --}}
                <div class="col-md-3 col-sm-6">
                    <div class="info-field-box">
                        <span class="field-label">gmail</span>
                        <div class="field-value text-dark text-truncate" title="{{ $student->personal_email ?: $student->ofc_mail }}">
                            {{ $student->personal_email ?: ($student->ofc_mail ?: '—') }}
                        </div>
                    </div>
                </div>

                {{-- 5. present status --}}
                <div class="col-md-2 col-sm-12">
                    <div class="info-field-box">
                        <span class="field-label">present status</span>
                        <div class="field-value">
                            <span class="badge {{ $meta['badge_class'] }} px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1">
                                <i class="bi {{ $meta['icon'] }}"></i> {{ $meta['label'] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Program & Batch context row --}}
                <div class="col-12 pt-1 border-top mt-1 d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                    <div>
                        <span class="fw-semibold text-dark">Program:</span> {{ $student->program?->program_name ?? ($student->program?->program_code ?? 'N/A') }}
                    </div>
                    <div>
                        <span class="fw-semibold text-dark">Batch:</span> {{ $student->adm_batch_code ?: ($student->admyear ? (string)$student->admyear : 'N/A') }}
                    </div>
                </div>
            </div>

            {{-- Update Status Action Section (NO POPUP CONFIRMATION) --}}
            <div class="update-action-box p-3 rounded-2 border">
                <form action="{{ route('admissions.stdstatusupdate.update') }}" method="POST" id="statusUpdateForm">
                    @csrf
                    <input type="hidden" name="application_no" value="{{ $student->application_no }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    @if (request()->filled('table_search'))
                        <input type="hidden" name="table_search" value="{{ request('table_search') }}">
                    @endif

                    <div class="row g-3 align-items-end">
                        {{-- Dropdown --}}
                        <div class="col-md-4 col-sm-12">
                            <label for="newStatusSelect" class="form-label fw-bold small text-dark mb-1">
                                Update status <span class="text-danger">*</span>
                            </label>
                            <select name="new_status" id="newStatusSelect" class="form-select status-dropdown fw-semibold" required>
                                @foreach ($statuses as $statusCode => $st)
                                    <option value="{{ $statusCode }}" {{ $normalizedCurrent === $statusCode ? 'selected' : '' }}>
                                        {{ $st['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Remarks --}}
                        <div class="col-md-5 col-sm-12">
                            <label for="statusRemarks" class="form-label fw-bold small text-dark mb-1">
                                Remarks <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <input
                                type="text"
                                name="remarks"
                                id="statusRemarks"
                                class="form-control"
                                placeholder="Optional reason or note for status update...">
                        </div>

                        {{-- Submit button (Direct submit, no popup) --}}
                        <div class="col-md-3 col-sm-12">
                            <button type="submit" class="btn btn-primary w-100 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm" id="updateStatusBtn" style="height: 38px;">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Update Status</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ================= 2. STUDENT STATUS RECORDS (PAGINATED) ================= --}}
    <div class="history-card p-3 rounded-3 border shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 14px;">
                    <i class="bi bi-people-fill text-primary"></i> Student Status Records
                </h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small fw-semibold">
                    Total: {{ number_format($students->total()) }} Records
                </span>
            </div>

            <div class="d-flex align-items-center gap-2.5 flex-wrap">
                {{-- Quick Table Filter Search --}}
                <form action="{{ route('admissions.stdstatusupdate') }}" method="GET" class="d-inline-flex align-items-center m-0">
                    @if (request()->filled('regno'))
                        <input type="hidden" name="regno" value="{{ request('regno') }}">
                    @endif
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <div class="input-group input-group-sm" style="width: 230px;">
                        <input
                            type="text"
                            name="table_search"
                            class="form-control"
                            placeholder="Filter table..."
                            value="{{ $tableSearch }}"
                            style="font-size: 12.5px;">
                        @if ($tableSearch)
                            <a href="{{ route('admissions.stdstatusupdate', array_merge(request()->except(['table_search', 'page']), ['per_page' => $perPage])) }}" class="btn btn-outline-secondary" title="Clear filter">
                                <i class="bi bi-x"></i>
                            </a>
                        @endif
                        <button class="btn btn-outline-secondary" type="submit" title="Filter list">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>

                {{-- Editable Per-Page Input Box (Cannot exceed max) --}}
                <form id="perPageForm" action="{{ route('admissions.stdstatusupdate') }}" method="GET" class="d-inline-flex align-items-center m-0">
                    @if (request()->filled('regno'))
                        <input type="hidden" name="regno" value="{{ request('regno') }}">
                    @endif
                    @if (request()->filled('table_search'))
                        <input type="hidden" name="table_search" value="{{ request('table_search') }}">
                    @endif
                    <div class="per-page-badge d-inline-flex align-items-center gap-1.5 py-1 px-2.5 border rounded-2"
                         title="Change records per page (1 to {{ $maxAllowed }})">
                        <label for="perPageInput" class="small fw-semibold text-muted text-nowrap mb-0" style="font-size: 12px; cursor: pointer;">
                            Show
                        </label>
                        <input
                            type="number"
                            name="per_page"
                            id="perPageInput"
                            class="form-control form-control-sm text-center fw-bold text-primary per-page-input-box"
                            value="{{ min($perPage, $maxAllowed) }}"
                            min="1"
                            max="{{ $maxAllowed }}"
                            data-max="{{ $maxAllowed }}"
                            title="Edit records per page (1 to {{ $maxAllowed }}) and press Enter"
                            aria-label="Records per page">
                        <span class="text-muted small text-nowrap" style="font-size: 12px;">
                            / <strong class="text-dark">{{ number_format($maxAllowed) }}</strong> max
                        </span>
                    </div>
                </form>
            </div>
        </div>

        {{-- Optional Recent Searched Chips if available --}}
        @if (!empty($searchedHistory))
            <div class="recent-chips-bar d-flex align-items-center gap-1.5 flex-wrap pb-2.5 mb-2.5 border-bottom">
                <span class="text-muted smaller fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11.5px;">
                    <i class="bi bi-clock-history"></i> Recent:
                </span>
                @foreach ($searchedHistory as $h)
                    @php
                        $hReg = $h['reg_no'] ?? ($h['application_no'] ?? '');
                    @endphp
                    @if ($hReg)
                        <a href="{{ route('admissions.stdstatusupdate', array_merge(request()->except('page'), ['regno' => $hReg])) }}"
                           class="badge bg-light text-dark border text-decoration-none py-1 px-2 d-inline-flex align-items-center gap-1 recent-chip {{ $searchedRegNo === $hReg ? 'border-primary text-primary fw-bold' : '' }}"
                           title="{{ $h['name'] }} ({{ $h['status'] }})">
                            <span class="font-monospace">{{ $hReg }}</span>
                            <span class="text-truncate" style="max-width: 100px;">{{ $h['name'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif

        @if ($students->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 history-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 14%;">Reg No</th>
                            <th style="width: 18%;">Name</th>
                            <th style="width: 13%;">phno</th>
                            <th style="width: 20%;">gmail</th>
                            <th style="width: 13%;">present status</th>
                            <th style="width: 17%;" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $idx => $item)
                            @php
                                $itemStatus = \Modules\Admissions\Http\Controllers\StudentStatusUpdateController::normalizeStatus($item->std_status ?? 'ACTIVE');
                                $itemMeta = $statuses[$itemStatus] ?? [
                                    'label' => $itemStatus,
                                    'badge_class' => 'badge-status-active',
                                    'icon' => 'bi-check-circle-fill',
                                ];
                                $reg = $item->register_no ?: $item->application_no;
                                $isSelected = ($student && ($student->application_no === $item->application_no || $student->register_no === $item->register_no));
                            @endphp
                            <tr class="{{ $isSelected ? 'table-primary-subtle' : '' }}">
                                <td class="text-muted small fw-semibold">
                                    {{ ($students->currentPage() - 1) * $students->perPage() + $idx + 1 }}
                                </td>
                                <td>
                                    <a href="{{ route('admissions.stdstatusupdate', array_merge(request()->query(), ['regno' => $reg])) }}" class="font-monospace fw-bold text-primary text-decoration-none">
                                        {{ $reg }}
                                    </a>
                                </td>
                                <td class="fw-semibold text-dark text-truncate" style="max-width: 180px;" title="{{ $item->student_name }}">
                                    {{ $item->student_name }}
                                </td>
                                <td class="text-dark small">
                                    {{ $item->mobile ?: '—' }}
                                </td>
                                <td class="small text-muted text-truncate" style="max-width: 190px;" title="{{ $item->personal_email ?: $item->ofc_mail }}">
                                    {{ $item->personal_email ?: ($item->ofc_mail ?: '—') }}
                                </td>
                                <td>
                                    <span class="badge {{ $itemMeta['badge_class'] }} px-2 py-1 fw-semibold small d-inline-flex align-items-center gap-1">
                                        <i class="bi {{ $itemMeta['icon'] }}"></i> {{ $itemMeta['label'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admissions.stdstatusupdate', array_merge(request()->query(), ['regno' => $reg])) }}"
                                       class="btn btn-sm {{ $isSelected ? 'btn-primary text-white' : 'btn-outline-primary' }} py-0.5 px-2 fw-semibold d-inline-flex align-items-center gap-1"
                                       style="font-size: 12px;">
                                        <i class="bi {{ $isSelected ? 'bi-check-circle-fill' : 'bi-pencil-square' }}"></i>
                                        {{ $isSelected ? 'Selected' : 'Select' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination footer with Bootstrap 5 links --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top mt-2">
                <span class="text-muted small">
                    Showing <strong>{{ $students->firstItem() ?? 0 }}</strong> to <strong>{{ $students->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($students->total()) }}</strong> records
                </span>
                <div class="pagination-wrapper">
                    {{ $students->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-inbox fs-3 d-block mb-1 text-muted opacity-50"></i>
                No student records found matching the criteria.
            </div>
        @endif
    </div>

</div>

{{-- ================= STYLES ================= --}}
<style>
    .std-status-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }

    /* Search Card */
    .search-card {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-card, #e2e8f0);
        border-radius: 10px;
    }
    .search-input-group {
        border-radius: 8px;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
    }
    .search-input-group .form-control {
        font-size: 14px;
        padding: 9px 12px;
    }
    .search-input-group .form-control:focus {
        border-color: #4f46e5;
        box-shadow: none;
    }

    /* Autocomplete dropdown */
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        z-index: 999;
        margin-top: 3px;
        max-height: 280px;
        overflow-y: auto;
    }
    .ac-item {
        padding: 8px 14px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: background .12s ease;
    }
    .ac-item:hover {
        background: #f8fafc;
    }
    .ac-reg {
        font-family: monospace;
        font-weight: 700;
        color: #4f46e5;
    }
    .ac-name {
        font-weight: 600;
        color: #0f172a;
    }

    /* Student Card */
    .status-student-card {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-card, #e2e8f0);
    }
    .student-info-grid {
        background: rgba(248, 250, 252, 0.7);
        border: 1px solid var(--border-card, #e2e8f0);
    }
    .info-field-box .field-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted, #64748b);
        text-transform: uppercase;
        letter-spacing: .5px;
        display: block;
        margin-bottom: 2px;
    }
    .info-field-box .field-value {
        font-size: 13.5px;
        color: var(--text-primary, #0f172a);
    }

    /* Status Badges for ACTIVE, DISCONTINUED, GRADUATED */
    .badge-status-active,
    .badge-status-admitted {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .badge-status-discontinued,
    .badge-status-debarred,
    .badge-status-suspended,
    .badge-status-fee-blocked,
    .badge-status-semester-break,
    .badge-status-study-break {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }
    .badge-status-graduated {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
        border: 1px solid rgba(59, 130, 246, 0.25);
    }

    /* Update Action Box */
    .update-action-box {
        background: #ffffff;
        border: 1px solid var(--border-card, #e2e8f0);
    }
    .status-dropdown {
        font-size: 14px;
        border-color: #cbd5e1;
    }
    .status-dropdown:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    /* History Table */
    .history-card {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-card, #e2e8f0);
    }
    .history-table th {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--text-muted, #64748b);
        background: rgba(248, 250, 252, 0.7);
        border-bottom: 2px solid var(--border-card, #e2e8f0);
        padding: 8px 12px;
    }
    .history-table td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--border-card, #e2e8f0);
        font-size: 13px;
    }

    /* Per-Page Editable Box */
    .per-page-badge {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-card, #cbd5e1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .per-page-badge:focus-within {
        border-color: var(--theme-primary, #4f46e5);
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15);
    }
    .per-page-input-box {
        width: 70px;
        height: 28px;
        padding: 2px 6px;
        font-size: 13px;
        font-weight: 700;
        color: var(--theme-primary, #4f46e5);
        border: 1px solid var(--border-subtle, #cbd5e1);
        border-radius: 5px;
        background: var(--bg-main, #ffffff);
    }
    .per-page-input-box:focus {
        border-color: var(--theme-primary, #4f46e5);
        box-shadow: none;
    }
    .table-primary-subtle {
        background-color: rgba(79, 70, 229, 0.08) !important;
    }
    .recent-chip {
        font-size: 11px;
        transition: all 0.15s ease;
    }
    .recent-chip:hover {
        background: rgba(79, 70, 229, 0.1) !important;
        color: var(--theme-primary, #4f46e5) !important;
        border-color: var(--theme-primary, #4f46e5) !important;
    }
</style>

{{-- ================= SCRIPTS ================= --}}
<script>
    // Instant AJAX Autocomplete for Search Box
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('studentRegNoInput');
        const dropdown = document.getElementById('autocompleteDropdown');
        const spinner = document.getElementById('searchSpinner');
        let debounceTimer;

        if (!input || !dropdown) return;

        input.addEventListener('input', function () {
            const query = this.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 2) {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                return;
            }

            if (spinner) spinner.classList.remove('d-none');

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('admissions.stdstatusupdate.lookup') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (spinner) spinner.classList.add('d-none');
                        if (!data.results || data.results.length === 0) {
                            dropdown.style.display = 'none';
                            dropdown.innerHTML = '';
                            return;
                        }

                        let html = '';
                        data.results.forEach(item => {
                            const reg = item.register_no || item.application_no;
                            html += `
                                <div class="ac-item" onclick="selectStudent('${reg}')">
                                    <div>
                                        <span class="ac-reg">${reg}</span>
                                        <span class="ac-name ms-2">${item.student_name}</span>
                                    </div>
                                    <div>
                                        <span class="badge bg-light text-dark border">${item.std_status}</span>
                                    </div>
                                </div>
                            `;
                        });
                        dropdown.innerHTML = html;
                        dropdown.style.display = 'block';
                    })
                    .catch(() => {
                        if (spinner) spinner.classList.add('d-none');
                    });
            }, 250);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    });

    function selectStudent(regNo) {
        const input = document.getElementById('studentRegNoInput');
        const form = document.getElementById('statusLookupForm');
        if (input && form) {
            input.value = regNo;
            document.getElementById('autocompleteDropdown').style.display = 'none';
            form.submit();
        }
    }

    // Editable Per-Page Input: Enforce max records count and submit on change / Enter
    (function () {
        const perPageInput = document.getElementById('perPageInput');
        const perPageForm = document.getElementById('perPageForm');
        if (!perPageInput || !perPageForm) return;

        const maxVal = parseInt(perPageInput.getAttribute('data-max') || perPageInput.getAttribute('max') || '1');
        const minVal = 1;
        const initialVal = parseInt(perPageInput.value) || 10;

        // Real-time prevention: user cannot type any number greater than max records count
        perPageInput.addEventListener('input', function () {
            if (this.value === '') return;
            let num = parseInt(this.value);
            if (isNaN(num)) {
                this.value = minVal;
            } else if (num > maxVal) {
                this.value = maxVal;
            } else if (num < minVal) {
                this.value = minVal;
            }
        });

        function submitIfChanged() {
            let num = parseInt(perPageInput.value);
            if (isNaN(num) || num < minVal) {
                num = minVal;
                perPageInput.value = minVal;
            } else if (num > maxVal) {
                num = maxVal;
                perPageInput.value = maxVal;
            }

            if (num !== initialVal) {
                perPageForm.submit();
            }
        }

        perPageInput.addEventListener('blur', submitIfChanged);
        perPageInput.addEventListener('change', submitIfChanged);
        perPageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitIfChanged();
            }
        });
    })();
</script>
@endsection
