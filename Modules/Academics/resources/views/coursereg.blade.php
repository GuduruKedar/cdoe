@extends('layouts.app')

@section('page_title', 'Course Registration Download')

@section('content')
<div class="cdoe-coursereg-container">

    {{-- ================= 1. FILTER TOOLBAR (Matches Image 2) ================= --}}
    <div class="filter-toolbar-box mb-3">
        <form method="GET" action="{{ route('academics.coursereg') }}" id="filterForm">
            <div class="row g-2 align-items-end">

                {{-- Semester Code --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-calendar4-week me-1"></i> Sem Code
                    </label>
                    <select name="semcode" class="form-select filter-select-blue">
                        <option value="all">Select Sem Code ▼</option>
                        @foreach($semcodes as $sc)
                            <option value="{{ $sc }}" {{ $semcode === $sc ? 'selected' : '' }}>
                                {{ $sc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject Code --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-book-half me-1"></i> Subject Code
                    </label>
                    <select name="subcode" class="form-select filter-select-blue">
                        <option value="all">Select Subject ▼</option>
                        @foreach($subcodes as $sub)
                            <option value="{{ $sub }}" {{ $subcode === $sub ? 'selected' : '' }}>
                                {{ $sub }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Year --}}
                <div class="col-xl-1 col-lg-2 col-md-2 col-sm-4 col-6">
                    <label class="filter-label">
                        <i class="bi bi-calendar-event me-1"></i> Year
                    </label>
                    <select name="year" class="form-select filter-select-blue">
                        <option value="all">Year ▼</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ (string)$year === (string)$y ? 'selected' : '' }}>
                                Year {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Semester --}}
                <div class="col-xl-1 col-lg-2 col-md-2 col-sm-4 col-6">
                    <label class="filter-label">
                        <i class="bi bi-clock-history me-1"></i> Sem
                    </label>
                    <select name="sem" class="form-select filter-select-blue">
                        <option value="all">Sem ▼</option>
                        @foreach($sems as $sm)
                            <option value="{{ $sm }}" {{ (string)$sem === (string)$sm ? 'selected' : '' }}>
                                Sem {{ $sm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Section --}}
                <div class="col-xl-1 col-lg-2 col-md-2 col-sm-4 col-6">
                    <label class="filter-label">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> Sec
                    </label>
                    <select name="section" class="form-select filter-select-blue">
                        <option value="all">Sec ▼</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec }}" {{ $section === $sec ? 'selected' : '' }}>
                                {{ $sec }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Reg Type --}}
                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 col-6">
                    <label class="filter-label">
                        <i class="bi bi-tag-fill me-1"></i> Reg Type
                    </label>
                    <select name="reg_type" class="form-select filter-select-blue">
                        <option value="all">Select Reg Type ▼</option>
                        @foreach($regTypes as $rt)
                            <option value="{{ $rt }}" {{ $regType === $rt ? 'selected' : '' }}>
                                {{ $rt }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search Input --}}
                <div class="col-xl-3 col-lg-5 col-md-5 col-sm-8 col-12">
                    <label class="filter-label">
                        <i class="bi bi-search me-1"></i> Search Reg No / Name
                    </label>
                    <input type="text"
                           name="q"
                           class="form-control filter-input-search"
                           placeholder="Search Reg No, App No, Name..."
                           value="{{ $q ?? '' }}">
                </div>

                {{-- Action Buttons Row (Matches Image 2 precisely) --}}
                <div class="col-12 mt-2 pt-2 border-top d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <button type="submit" class="btn btn-action btn-apply">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('academics.coursereg') }}" class="btn btn-action btn-clear">
                        <i class="bi bi-trash3-fill me-1"></i> Clear All
                    </a>
                    <button type="button" class="btn btn-action btn-print" onclick="window.print()">
                        <i class="bi bi-printer-fill me-1"></i> Print
                    </button>
                    <a href="{{ route('academics.coursereg.export', request()->all()) }}" class="btn btn-action btn-download">
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
                @if(empty($semcode) && empty($subcode) && empty($year) && empty($sem) && empty($section) && empty($regType) && empty($q) || ($semcode === 'all' && $subcode === 'all' && $year === 'all' && $sem === 'all' && $section === 'all' && $regType === 'all' && empty($q)))
                    <span class="text-muted fw-normal">No filters applied (showing all records)</span>
                @else
                    <span class="text-primary fw-bold">
                        {{ $semcode && $semcode !== 'all' ? 'Sem: ' . $semcode . ' | ' : '' }}
                        {{ $subcode && $subcode !== 'all' ? 'Sub: ' . $subcode . ' | ' : '' }}
                        {{ $year && $year !== 'all' ? 'Year: ' . $year . ' | ' : '' }}
                        {{ $sem && $sem !== 'all' ? 'Sem: ' . $sem . ' | ' : '' }}
                        {{ $section && $section !== 'all' ? 'Sec: ' . $section . ' | ' : '' }}
                        {{ $regType && $regType !== 'all' ? 'Type: ' . $regType . ' | ' : '' }}
                        {{ $q ? 'Query: "' . $q . '"' : '' }}
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

    {{-- ================= 3. COURSE REGISTRATION TABLE (Matches Image 2) ================= --}}
    <div class="table-card-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 cdoe-pink-table">
                <thead>
                    <tr>
                        <th style="min-width: 50px;">S.No.</th>
                        <th style="min-width: 95px;">Sem Code</th>
                        <th style="min-width: 80px;">Class No</th>
                        <th style="min-width: 85px;">App No</th>
                        <th style="min-width: 105px;">Reg No</th>
                        <th style="min-width: 170px;">Student Name</th>
                        <th style="min-width: 95px;">Sub Code</th>
                        <th style="min-width: 90px;">Study Place</th>
                        <th style="min-width: 80px;">Room No</th>
                        <th style="min-width: 65px; text-align: center;">Year</th>
                        <th style="min-width: 65px; text-align: center;">Sem</th>
                        <th style="min-width: 65px; text-align: center;">Sec</th>
                        <th style="min-width: 95px;">Regulation</th>
                        <th style="min-width: 95px;">Reg Date</th>
                        <th style="min-width: 95px; text-align: center;">Reg Type</th>
                        <th style="min-width: 90px;">Core Emp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $idx => $r)
                        <tr>
                            <td class="text-center font-monospace text-muted">{{ $records->firstItem() + $idx }}</td>
                            <td><span class="badge bg-primary-subtle text-primary font-monospace">{{ $r->semcode }}</span></td>
                            <td class="font-monospace small">{{ $r->classno }}</td>
                            <td class="font-monospace text-muted">{{ $r->appno }}</td>
                            <td><strong class="font-monospace text-primary">{{ $r->regno }}</strong></td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 180px;" title="{{ $r->student_name }}">
                                    {{ $r->student_name ?: '—' }}
                                </div>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $r->subcode }}</span></td>
                            <td class="small">{{ $r->stdplace ?: '—' }}</td>
                            <td class="font-monospace small">{{ $r->roomno ?: '—' }}</td>
                            <td class="text-center font-monospace">{{ $r->s_year ?? '—' }}</td>
                            <td class="text-center font-monospace">{{ $r->s_sem ?? '—' }}</td>
                            <td class="text-center font-monospace">{{ $r->section_no ?: '—' }}</td>
                            <td class="font-monospace small">{{ $r->regulationid ?: '—' }}</td>
                            <td class="small text-muted">{{ $r->reg_date ? \Carbon\Carbon::parse($r->reg_date)->format('d-m-Y') : '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success rounded-pill font-monospace">
                                    {{ $r->reg_type ?: 'REGULAR' }}
                                </span>
                            </td>
                            <td class="font-monospace small text-muted">{{ $r->core_emp ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2 text-muted"></i>
                                No course registration records found matching the applied filters.
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
    /* Vivid Blue Pill Selects matching Image 2 */
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

    /* Action Buttons (Matches Image 2 colors and style) */
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

    /* Active Filters Ribbon matching Image 2 */
    .active-filters-ribbon {
        background-color: var(--bg-surface) !important;
        border: 1px solid var(--border-subtle) !important;
        border-radius: 8px;
        padding: 10px 18px;
        font-size: 12.5px;
        color: var(--text-primary) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }

    /* Table Container & Pink Table Header matching Image 2 */
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

    /* Pagination Footer */
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

    /* Prevent any SVG in pagination or cards from blowing up */
    .pagination svg,
    nav svg {
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
        .table-card-container {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection
