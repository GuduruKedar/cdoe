@extends('layouts.app')

@section('page_title', 'Sub CO-PO-PSO Mapping')

@section('content')
<div class="cdoe-subjects-container">

    {{-- ================= 1. FILTER TOOLBAR ================= --}}
    <div class="filter-toolbar-box mb-3">
        <form method="GET" action="{{ url()->current() }}" id="filterForm">
            <div class="row g-2 align-items-end">

                <div class="col-lg-4 col-md-5 col-sm-12">
                    <label class="filter-label">
                        <i class="bi bi-book-half me-1"></i> Course / Subject
                    </label>
                    <select name="subcode" class="form-select filter-select-blue">
                        <option value="all">-- All Subjects ({{ $subjectsList->count() }}) --</option>
                        @foreach($subjectsList as $s)
                            <option value="{{ $s->subcode }}" {{ $selectedSubject === $s->subcode ? 'selected' : '' }}>
                                {{ $s->subcode }} — {{ \Illuminate\Support\Str::limit($s->subname ?: 'Subject', 38) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-bullseye me-1"></i> Course Outcome (CO)
                    </label>
                    <select name="coname" class="form-select filter-select-blue">
                        <option value="all">-- All COs --</option>
                        @foreach($coList as $co)
                            <option value="{{ $co }}" {{ $selectedCo === $co ? 'selected' : '' }}>{{ $co }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-label">
                        <i class="bi bi-search me-1"></i> Search Query
                    </label>
                    <input type="text" name="q" class="form-control filter-input-search" placeholder="Subject code, name, or CO..." value="{{ $q ?? '' }}">
                </div>

                <div class="col-lg-3 col-md-12 text-end d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <button type="submit" class="btn btn-action btn-apply">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <a href="{{ url()->current() }}" class="btn btn-action btn-clear">
                        <i class="bi bi-trash3-fill me-1"></i> Clear All
                    </a>
                    <a href="{{ route('academics.subcopopso.export', request()->all()) }}" class="btn btn-action btn-download">
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ================= 2. ACTIVE FILTERS RIBBON ================= --}}
    <div class="active-filters-ribbon mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span>📌 <strong>Active Filters:</strong></span>
            @if(empty($selectedSubject) && empty($selectedCo) && empty($q) || ($selectedSubject === 'all' && $selectedCo === 'all' && empty($q)))
                <span class="text-muted fw-normal">Showing all curriculum outcome mappings across all subjects</span>
            @else
                <span class="text-primary fw-bold">
                    {{ $selectedSubject && $selectedSubject !== 'all' ? 'Subject: ' . $selectedSubject . ' | ' : '' }}
                    {{ $selectedCo && $selectedCo !== 'all' ? 'Outcome: ' . $selectedCo . ' | ' : '' }}
                    {{ $q ? 'Query: "' . $q . '"' : '' }}
                </span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3">
            <span>📊 <strong>Total Mappings:</strong> {{ $totalRecords }}</span>
            <span>|</span>
            <span>🔍 <strong>Filtered:</strong> {{ $filteredCount }}</span>
        </div>
    </div>

    {{-- ================= 3. CO-PO-PSO MAPPING MATRIX TABLE ================= --}}
    <div class="table-card-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 matrix-table">
                <thead>
                    <tr>
                        <th style="min-width: 50px; width: 50px;">S.No</th>
                        <th style="min-width: 110px;">Subject Code</th>
                        <th style="min-width: 220px;">Subject Name</th>
                        <th style="min-width: 70px; text-align: center;">CO</th>
                        <th style="min-width: 45px; text-align: center;">PO1</th>
                        <th style="min-width: 45px; text-align: center;">PO2</th>
                        <th style="min-width: 45px; text-align: center;">PO3</th>
                        <th style="min-width: 45px; text-align: center;">PO4</th>
                        <th style="min-width: 45px; text-align: center;">PO5</th>
                        <th style="min-width: 45px; text-align: center;">PO6</th>
                        <th style="min-width: 45px; text-align: center;">PO7</th>
                        <th style="min-width: 45px; text-align: center;">PO8</th>
                        <th style="min-width: 45px; text-align: center;">PO9</th>
                        <th style="min-width: 50px; text-align: center;">PO10</th>
                        <th style="min-width: 50px; text-align: center;">PSO1</th>
                        <th style="min-width: 50px; text-align: center;">PSO2</th>
                        <th style="min-width: 50px; text-align: center;">PSO3</th>
                        <th style="min-width: 50px; text-align: center;">PSO4</th>
                        <th style="min-width: 50px; text-align: center;">PSO5</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mappings as $idx => $m)
                        <tr>
                            <td class="text-center font-monospace text-muted">{{ $mappings->firstItem() + $idx }}</td>
                            <td><strong class="font-monospace text-primary">{{ $m->subcode }}</strong></td>
                            <td class="fw-semibold">{{ $m->subname ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary fw-bold font-monospace px-2 py-1">{{ $m->coname }}</span>
                            </td>

                            {{-- PO1 to PO10 --}}
                            @for($i = 1; $i <= 10; $i++)
                                @php
                                    $val = floatval($m->{'po'.$i} ?? 0);
                                @endphp
                                <td class="text-center font-monospace {{ $val > 0 ? 'fw-bold' : 'text-muted' }}" style="{{ $val >= 3 ? 'background-color: rgba(25, 135, 84, 0.12); color: #198754 !important;' : ($val >= 2 ? 'background-color: rgba(13, 110, 253, 0.08);' : ($val >= 1 ? 'background-color: rgba(255, 193, 7, 0.08);' : 'opacity: 0.5;')) }}">
                                    {{ $val > 0 ? number_format($val, 0) : '—' }}
                                </td>
                            @endfor

                            {{-- PSO1 to PSO5 --}}
                            @for($i = 1; $i <= 5; $i++)
                                @php
                                    $psoVal = floatval($m->{'pso'.$i} ?? 0);
                                @endphp
                                <td class="text-center font-monospace {{ $psoVal > 0 ? 'fw-bold' : 'text-muted' }}" style="{{ $psoVal >= 3 ? 'background-color: rgba(111, 66, 193, 0.12); color: #6f42c1 !important;' : ($psoVal >= 2 ? 'background-color: rgba(13, 202, 240, 0.08);' : ($psoVal >= 1 ? 'background-color: rgba(255, 193, 7, 0.08);' : 'opacity: 0.5;')) }}">
                                    {{ $psoVal > 0 ? number_format($psoVal, 0) : '—' }}
                                </td>
                            @endfor
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="text-center py-5 text-muted">
                                <i class="bi bi-grid-3x3 display-6 d-block mb-2 text-muted"></i>
                                No CO-PO-PSO mappings found matching the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-pagination-footer">
            <span class="text-muted small">Showing {{ $mappings->firstItem() ?? 0 }} to {{ $mappings->lastItem() ?? 0 }} of {{ $mappings->total() }} mappings</span>
            <div class="pagination-wrapper">
                {{ $mappings->withQueryString()->links('pagination::bootstrap-5') }}
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
    .matrix-table {
        color: var(--text-primary);
        border-color: var(--border-subtle) !important;
        margin-bottom: 0;
    }
    .matrix-table thead th {
        background: var(--theme-gradient, linear-gradient(135deg, #9b111e 0%, #c1121f 50%, #780000 100%)) !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        padding: 11px 8px;
        text-transform: uppercase;
        border-color: rgba(255, 255, 255, 0.15) !important;
        letter-spacing: 0.3px;
    }
    .matrix-table tbody td {
        font-size: 12.5px;
        padding: 9px 8px;
        border-color: var(--border-subtle) !important;
        vertical-align: middle;
        color: var(--text-primary);
    }
    .matrix-table tbody tr:hover td { background-color: var(--bg-hover-row) !important; }
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
