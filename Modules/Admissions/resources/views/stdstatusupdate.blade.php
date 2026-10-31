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
                'badge_class' => 'badge-status-admitted',
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

    {{-- ================= 2. HISTORY OF PAST 10 SEARCHED STUDENTS ================= --}}
    <div class="history-card p-3 rounded-3 border shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-2.5 flex-wrap gap-2">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 14px;">
                <i class="bi bi-clock-history text-primary"></i> History of Past 10 Searched Students
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                Showing {{ count($searchedHistory) }} Records
            </span>
        </div>

        @if (!empty($searchedHistory))
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
                        @foreach ($searchedHistory as $idx => $item)
                            @php
                                $itemStatus = \Modules\Admissions\Http\Controllers\StudentStatusUpdateController::normalizeStatus($item['status'] ?? 'Active / Admitted');
                                $itemMeta = $statuses[$itemStatus] ?? [
                                    'label' => $itemStatus,
                                    'badge_class' => 'badge-status-admitted',
                                    'icon' => 'bi-check-circle-fill',
                                ];
                                $reg = $item['reg_no'] ?? ($item['application_no'] ?? '');
                            @endphp
                            <tr>
                                <td class="text-muted small fw-semibold">{{ $idx + 1 }}</td>
                                <td>
                                    <a href="{{ route('admissions.stdstatusupdate', ['regno' => $reg]) }}" class="font-monospace fw-bold text-primary text-decoration-none">
                                        {{ $reg }}
                                    </a>
                                </td>
                                <td class="fw-semibold text-dark text-truncate" style="max-width: 180px;" title="{{ $item['name'] }}">
                                    {{ $item['name'] }}
                                </td>
                                <td class="text-dark small">
                                    {{ $item['phone'] }}
                                </td>
                                <td class="small text-muted text-truncate" style="max-width: 190px;" title="{{ $item['email'] }}">
                                    {{ $item['email'] }}
                                </td>
                                <td>
                                    <span class="badge {{ $itemMeta['badge_class'] }} px-2 py-1 fw-semibold small d-inline-flex align-items-center gap-1">
                                        <i class="bi {{ $itemMeta['icon'] }}"></i> {{ $itemMeta['label'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admissions.stdstatusupdate', ['regno' => $reg]) }}" class="btn btn-sm btn-outline-primary py-0.5 px-2 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 12px;">
                                        <i class="bi bi-pencil-square"></i> Select
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-inbox fs-3 d-block mb-1 text-muted opacity-50"></i>
                No searched students recorded yet.
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

    /* Status Badges */
    .badge-status-admitted {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .badge-status-debarred {
        background: rgba(239, 68, 68, 0.12);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }
    .badge-status-suspended {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .badge-status-semester-break {
        background: rgba(6, 182, 212, 0.12);
        color: #0891b2;
        border: 1px solid rgba(6, 182, 212, 0.25);
    }
    .badge-status-study-break {
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
        border: 1px solid rgba(59, 130, 246, 0.25);
    }
    .badge-status-fee-blocked {
        background: rgba(100, 116, 139, 0.12);
        color: #475569;
        border: 1px solid rgba(100, 116, 139, 0.25);
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
</script>
@endsection
