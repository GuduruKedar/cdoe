@extends('layouts.app')

@section('page_title', 'Curriculum & Subject List')

@section('content')
<div class="cdoe-subjects-container">

    {{-- Filter Toolbar --}}
    <div class="filter-toolbar-box mb-3">
        <form method="GET" action="{{ url()->current() }}" id="filterForm">
            <div class="row g-2 align-items-end">

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-tag-fill me-1"></i> Subject Type
                    </label>
                    <select name="subtype" class="form-select filter-select-blue">
                        <option value="all">Select Subject Type ▼</option>
                        @foreach($subtypes as $st)
                            <option value="{{ $st }}" {{ $type === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-display me-1"></i> Delivery Mode
                    </label>
                    <select name="submode" class="form-select filter-select-blue">
                        <option value="all">Select Delivery Mode ▼</option>
                        @foreach($submodes as $sm)
                            <option value="{{ $sm }}" {{ $mode === $sm ? 'selected' : '' }}>{{ $sm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-12">
                    <label class="filter-label">
                        <i class="bi bi-search me-1"></i> Search
                    </label>
                    <input type="text" name="q" class="form-control filter-input-search" placeholder="Subject code or title..." value="{{ $q ?? '' }}">
                </div>

                <div class="col-lg-3 col-md-12 text-end d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <button type="submit" class="btn btn-action btn-apply">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <a href="{{ url()->current() }}" class="btn btn-action btn-clear">
                        <i class="bi bi-trash3-fill me-1"></i> Clear All
                    </a>
                    <a href="{{ request()->is('*subjectsview*') ? route('academics.subjectsview.export', request()->all()) : route('academics.subjectlist.export', request()->all()) }}" class="btn btn-action btn-download">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Banner --}}
    <div class="active-filters-ribbon mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span>📌 <strong>Curriculum Catalog:</strong></span>
            <span class="text-muted small">Showing accredited courses and subject codes</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span>📊 <strong>Total Subjects:</strong> {{ $totalRecords }}</span>
            <span>|</span>
            <span>🔍 <strong>Filtered:</strong> {{ $filteredCount }}</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-card-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 cdoe-pink-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">S.No.</th>
                        <th style="width: 120px;">Subject Code</th>
                        <th>Subject Name</th>
                        <th style="width: 140px;">Type</th>
                        <th style="width: 60px; text-align: center;">L</th>
                        <th style="width: 60px; text-align: center;">T</th>
                        <th style="width: 60px; text-align: center;">P</th>
                        <th style="width: 60px; text-align: center;">C</th>
                        <th style="width: 80px; text-align: center;">Credits</th>
                        <th style="width: 120px; text-align: center;">Mode</th>
                        <th style="width: 110px;">Effective Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $idx => $s)
                        <tr>
                            <td class="text-center font-monospace text-muted">{{ $subjects->firstItem() + $idx }}</td>
                            <td><strong class="font-monospace text-primary">{{ $s->subcode }}</strong></td>
                            <td class="fw-semibold">{{ $s->subname }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $s->subtype ?: 'THEORY' }}</span></td>
                            <td class="text-center font-monospace">{{ $s->l }}</td>
                            <td class="text-center font-monospace">{{ $s->t }}</td>
                            <td class="text-center font-monospace">{{ $s->p }}</td>
                            <td class="text-center font-monospace">{{ $s->c }}</td>
                            <td class="text-center font-monospace fw-bold text-success">{{ $s->credits }}</td>
                            <td class="text-center"><span class="badge bg-info-subtle text-info font-monospace">{{ $s->submode ?: 'REGULAR' }}</span></td>
                            <td class="small text-muted">{{ $s->subdate ? \Carbon\Carbon::parse($s->subdate)->format('d-m-Y') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">No subjects found matching query.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-pagination-footer">
            <span class="text-muted small">Showing {{ $subjects->firstItem() ?? 0 }} to {{ $subjects->lastItem() ?? 0 }} of {{ $subjects->total() }} subjects</span>
            <div class="pagination-wrapper">
                {{ $subjects->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<style>
    .filter-toolbar-box {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-card) !important;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: var(--card-shadow, 0 4px 18px rgba(0, 0, 0, 0.08)) !important;
        color: var(--text-primary);
    }
    .filter-label { font-size: 12px; font-weight: 700; color: var(--text-primary) !important; margin-bottom: 4px; display: flex; align-items: center; }
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
    }
    .filter-select-blue option { background-color: var(--bg-surface, #ffffff); color: var(--text-primary, #0f172a); }
    .filter-input-search {
        background-color: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-subtle) !important;
        border-radius: 6px;
        font-size: 13px;
        padding: 6px 12px;
    }
    .filter-input-search:focus {
        background-color: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2) !important;
    }
    .btn-action {
        border-radius: 6px; font-size: 13px; font-weight: 600; padding: 6px 16px; color: #ffffff !important;
        border: none; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        transition: transform 0.15s ease; text-decoration: none;
    }
    .btn-action:hover { transform: translateY(-1px); }
    .btn-apply { background-color: #0d6efd; }
    .btn-clear { background-color: #dc3545; }
    .btn-download { background-color: #fd7e14; }
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
        background-color: #e83e8c !important; color: #ffffff !important; font-size: 12px; font-weight: 700;
        padding: 11px 8px; text-transform: uppercase; border-color: #d63384 !important;
    }
    .cdoe-pink-table tbody td {
        font-size: 12.5px; padding: 9px 8px; border-color: var(--border-subtle) !important;
        vertical-align: middle; color: var(--text-primary); background: transparent !important;
    }
    .cdoe-pink-table tbody tr:hover td { background-color: var(--bg-hover-row) !important; }
    .table-pagination-footer {
        padding: 12px 20px; border-top: 1px solid var(--border-subtle); display: flex;
        justify-content: space-between; align-items: center; background: var(--bg-card) !important;
        color: var(--text-muted); flex-wrap: wrap; gap: 12px;
    }
    .pagination-wrapper .page-link {
        background-color: var(--bg-surface); color: var(--text-primary); border-color: var(--border-subtle);
        font-size: 12.5px; padding: 5px 12px;
    }
    .pagination-wrapper .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: #ffffff; }
    .pagination-wrapper .page-item.disabled .page-link { background-color: var(--bg-card); color: var(--text-muted); border-color: var(--border-subtle); }
    .pagination svg, nav svg { width: 14px !important; height: 14px !important; max-width: 14px !important; max-height: 14px !important; display: inline-block !important; }
</style>
@endsection
