@extends('layouts.app')

@section('page_title', 'Admission Data Download')

@section('content')
<div class="cdoe-data-download-container">

    {{-- ================= 1. FILTER TOOLBAR (Matches Image 2) ================= --}}
    <div class="filter-toolbar-box mb-3">
        <form method="GET" action="{{ route('admissions.datadownload') }}" id="filterForm">
            <div class="row g-2 align-items-end">

                {{-- Program / Department --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-mortarboard-fill me-1"></i> Program
                    </label>
                    <select name="program" class="form-select filter-select-blue">
                        <option value="all">Select Program ▼</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->program_id }}" {{ (string)$program === (string)$p->program_id ? 'selected' : '' }}>
                                {{ $p->program_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Batch --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-calendar3 me-1"></i> Batch
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

                {{-- Regulation --}}
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-journal-bookmark-fill me-1"></i> Regulation
                    </label>
                    <select name="regulation" class="form-select filter-select-blue">
                        <option value="all">Regulation ▼</option>
                        @foreach($regulations as $r)
                            <option value="{{ $r }}" {{ $regulation === $r ? 'selected' : '' }}>
                                {{ $r }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Gender --}}
                <div class="col-xl-1 col-lg-2 col-md-2 col-sm-4 col-6">
                    <label class="filter-label">
                        <i class="bi bi-gender-ambiguous me-1"></i> Gender
                    </label>
                    <select name="gender" class="form-select filter-select-blue">
                        <option value="all">Gender ▼</option>
                        <option value="M" {{ $gender === 'M' ? 'selected' : '' }}>M</option>
                        <option value="F" {{ $gender === 'F' ? 'selected' : '' }}>F</option>
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-4 col-6">
                    <label class="filter-label">
                        <i class="bi bi-flag-fill me-1"></i> Status
                    </label>
                    <select name="status" class="form-select filter-select-blue">
                        <option value="all">Status ▼</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search Input --}}
                <div class="col-xl-3 col-lg-4 col-md-5 col-sm-12">
                    <label class="filter-label">
                        <i class="bi bi-search me-1"></i> Search Student
                    </label>
                    <input type="text"
                           name="q"
                           class="form-control filter-input-search"
                           placeholder="Reg No, App No, Name, Mobile..."
                           value="{{ $q ?? '' }}">
                </div>

                {{-- Action Buttons Row (Matches Image 2 precisely) --}}
                <div class="col-12 mt-2 pt-2 border-top d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <button type="submit" class="btn btn-action btn-apply">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('admissions.datadownload') }}" class="btn btn-action btn-clear">
                        <i class="bi bi-trash3-fill me-1"></i> Clear All
                    </a>
                    <button type="button" class="btn btn-action btn-print" onclick="window.print()">
                        <i class="bi bi-printer-fill me-1"></i> Print
                    </button>
                    <a href="{{ route('admissions.datadownload.export', request()->all()) }}" class="btn btn-action btn-download">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-action btn-back">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ================= 2. ACTIVE FILTERS BANNER (Matches Image 2) ================= --}}
    <div class="active-filters-ribbon mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="ribbon-pin">📌</span>
            <span class="fw-semibold">
                Active Filters:
                @if(empty($program) && empty($batch) && empty($regulation) && empty($gender) && empty($status) && empty($q) || ($program === 'all' && $batch === 'all' && $regulation === 'all' && $gender === 'all' && $status === 'all' && empty($q)))
                    <span class="text-muted fw-normal">No filters applied (showing all records)</span>
                @else
                    <span class="text-primary fw-bold">
                        {{ $program && $program !== 'all' ? 'Program: ' . $program . ' | ' : '' }}
                        {{ $batch && $batch !== 'all' ? 'Batch: ' . $batch . ' | ' : '' }}
                        {{ $regulation && $regulation !== 'all' ? 'Regulation: ' . $regulation . ' | ' : '' }}
                        {{ $gender && $gender !== 'all' ? 'Gender: ' . $gender . ' | ' : '' }}
                        {{ $status && $status !== 'all' ? 'Status: ' . $status . ' | ' : '' }}
                        {{ $q ? 'Search: "' . $q . '"' : '' }}
                    </span>
                @endif
            </span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span>📊 <strong>Total Records:</strong> {{ $totalRecords }}</span>
            <span>|</span>
            <span>🔍 <strong>Filtered Records:</strong> {{ $filteredCount }}</span>
        </div>
    </div>

    {{-- ================= 3. REGISTRATION TABLE (Matches Image 2 Pink Header) ================= --}}
    <div class="table-card-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 cdoe-pink-table">
                <thead>
                    <tr>
                        <th style="min-width: 50px;">S.No.</th>
                        <th style="min-width: 90px;">App No</th>
                        <th style="min-width: 105px;">Reg No</th>
                        <th style="min-width: 170px;">Student Name</th>
                        <th style="min-width: 80px;">Program</th>
                        <th style="min-width: 160px;">Email</th>
                        <th style="min-width: 105px;">Mobile</th>
                        <th style="min-width: 65px; text-align: center;">Gender</th>
                        <th style="min-width: 90px;">DOB</th>
                        <th style="min-width: 90px;">DOJ</th>
                        <th style="min-width: 90px;">Batch</th>
                        <th style="min-width: 80px;">Reg</th>
                        <th style="min-width: 110px;">Aadhar No</th>
                        <th style="min-width: 100px;">ABC ID</th>
                        <th style="min-width: 100px;">DEB ID</th>
                        <th style="min-width: 80px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $idx => $r)
                        <tr>
                            <td class="text-center font-monospace text-muted">{{ $records->firstItem() + $idx }}</td>
                            <td class="font-monospace text-muted">{{ $r->application_no }}</td>
                            <td><strong class="font-monospace text-primary">{{ $r->register_no }}</strong></td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 180px;" title="{{ $r->student_name }}">
                                    {{ $r->student_name }}
                                </div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary font-monospace">{{ $r->program_code ?? '—' }}</span></td>
                            <td class="small text-truncate" style="max-width: 160px;" title="{{ $r->personal_email }}">
                                {{ $r->personal_email ?: '—' }}
                            </td>
                            <td class="font-monospace small">{{ $r->mobile ?: '—' }}</td>
                            <td class="text-center font-monospace">{{ $r->gender ?: '—' }}</td>
                            <td class="small text-muted">{{ $r->dob ? \Carbon\Carbon::parse($r->dob)->format('d-m-Y') : '—' }}</td>
                            <td class="small text-muted">{{ $r->doj ? \Carbon\Carbon::parse($r->doj)->format('d-m-Y') : '—' }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $r->adm_batch_code ?: '—' }}</span></td>
                            <td class="font-monospace small">{{ $r->regulation ?: '—' }}</td>
                            <td class="font-monospace small text-muted">{{ $r->aadhar_no ?: '—' }}</td>
                            <td class="font-monospace small text-muted">{{ $r->abc_id ?: '—' }}</td>
                            <td class="font-monospace small text-muted">{{ $r->deb_id ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ strtolower($r->std_status) === 'active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill">
                                    {{ $r->std_status ?: 'Active' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2 text-muted"></i>
                                No student registration records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination using Bootstrap 5 template --}}
        <div class="table-pagination-footer">
            <span class="text-muted small">
                Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} records
            </span>
            <div class="pagination-wrapper">
                {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<style>
    /* ================= THEME ADAPTIVE STYLING MATCHING IMAGE 2 ================= */
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
    .cdoe-pink-table {
        color: var(--text-primary);
        border-color: var(--border-subtle) !important;
        margin-bottom: 0;
    }
    .cdoe-pink-table thead th {
        background-color: #e83e8c !important;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 700;
        padding: 11px 8px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-color: #d63384 !important;
        white-space: nowrap;
        vertical-align: middle;
    }
    .cdoe-pink-table tbody td {
        font-size: 12.5px;
        padding: 9px 8px;
        border-color: var(--border-subtle) !important;
        vertical-align: middle;
        color: var(--text-primary);
        background: transparent !important;
    }
    .cdoe-pink-table tbody tr:hover td {
        background-color: var(--bg-hover-row) !important;
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
    .pagination-wrapper .pagination { margin-bottom: 0; }
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
    @media print {
        .filter-toolbar-box, .active-filters-ribbon, .table-pagination-footer, .topnav, .sidebar {
            display: none !important;
        }
    }
</style>
@endsection
