@extends('layouts.app')

@section('page_title', 'Exam Report')

@section('content')
<div class="cdoe-examreport-wrapper">

    {{-- ================= 1. PAGE HEADER ================= --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h1 class="page-main-heading mb-1 d-flex align-items-center gap-2">
                <span class="heading-icon-badge">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                </span>
                Exam Report
            </h1>
            <p class="text-muted small mb-0">
                Examination results tabulation, backlog fail reports, top performers & SGPA/CGPA analytics
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 fs-7">
                <i class="bi bi-calendar2-check me-1 text-primary"></i> Academic Year {{ date('Y') }}
            </span>
        </div>
    </div>

    {{-- ================= 2. SKETCH NAVIGATION TABS ================= --}}
    {{-- Matches hand-drawn wireframe sketch: [Fail Report] [Toppers] [CGPA Rep] [...] --}}
    <div class="sketch-tabs-container mb-3">
        <div class="sketch-tabs-nav d-flex align-items-center gap-2 flex-wrap">
            {{-- Tab 1: Fail Report --}}
            <a href="{{ route('examinations.examreport', array_merge(request()->except(['page']), ['tab' => 'fail'])) }}"
               class="sketch-tab-btn {{ $tab === 'fail' ? 'active tab-fail' : '' }}">
                <span class="tab-indicator red"></span>
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Fail Report
                @if(($counts['failed_subjects'] ?? 0) > 0)
                    <span class="sketch-tab-badge bg-danger">{{ number_format($counts['failed_subjects']) }}</span>
                @endif
            </a>

            {{-- Tab 2: Toppers --}}
            <a href="{{ route('examinations.examreport', array_merge(request()->except(['page']), ['tab' => 'toppers'])) }}"
               class="sketch-tab-btn {{ $tab === 'toppers' ? 'active tab-toppers' : '' }}">
                <span class="tab-indicator gold"></span>
                <i class="bi bi-trophy-fill me-1"></i> Toppers
                @if(($counts['toppers'] ?? 0) > 0)
                    <span class="sketch-tab-badge bg-warning text-dark">{{ number_format($counts['toppers']) }}</span>
                @endif
            </a>

            {{-- Tab 3: CGPA Report --}}
            <a href="{{ route('examinations.examreport', array_merge(request()->except(['page']), ['tab' => 'cgpa'])) }}"
               class="sketch-tab-btn {{ $tab === 'cgpa' ? 'active tab-cgpa' : '' }}">
                <span class="tab-indicator blue"></span>
                <i class="bi bi-graph-up-arrow me-1"></i> CGPA Report
                <span class="sketch-tab-badge bg-primary">{{ number_format($counts['total_students']) }}</span>
            </a>

            {{-- More Links Dropdown ("... etc" from sketch) --}}
            <div class="dropdown d-inline-block">
                <button class="sketch-tab-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots me-1"></i> More Reports
                </button>
                <ul class="dropdown-menu shadow-sm">
                    <li>
                        <a class="dropdown-item" href="{{ route('examinations.markskpi') }}">
                            <i class="bi bi-speedometer2 text-info me-2"></i> Marks KPI Dashboard
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('examinations.cgpamarks') }}">
                            <i class="bi bi-table text-primary me-2"></i> Raw CGPA Marks Tabulation
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('examinations.mmview') }}">
                            <i class="bi bi-card-checklist text-success me-2"></i> Individual Marks Memo
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ================= 3. FILTER TOOLBAR & EXPORT BUTTONS ================= --}}
    {{-- Matches sketch: [Program Filter 1] [Batch Filter 2] [Filter 3] [Search] ... [CSV] [Print] --}}
    <div class="sketch-filter-card mb-3">
        <form method="GET" action="{{ route('examinations.examreport') }}" id="examReportFilterForm">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="per_page" value="{{ $perPage }}">

            <div class="row g-2 align-items-end">

                {{-- Filter 1: Program (Program dropdown from sketch) --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-field-label">
                        <i class="bi bi-mortarboard-fill me-1 text-primary"></i> Program (Filter 1)
                    </label>
                    <select name="program" class="form-select filter-select-custom" onchange="this.form.submit()">
                        <option value="all">All Programs</option>
                        @foreach($programs as $p)
                            <option value="{{ $p->program_id }}" {{ (string)$program === (string)$p->program_id ? 'selected' : '' }}>
                                {{ $p->program_short_name ?: $p->program_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter 2: Batch (Batch dropdown from sketch) --}}
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="filter-field-label">
                        <i class="bi bi-people-fill me-1 text-primary"></i> Batch (Filter 2)
                    </label>
                    <select name="batch" class="form-select filter-select-custom" onchange="this.form.submit()">
                        <option value="all">All Batches</option>
                        @foreach($batches as $b)
                            <option value="{{ $b }}" {{ $batch === $b ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', $b) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter 3: Semester (Filter 3 from sketch) --}}
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                    <label class="filter-field-label">
                        <i class="bi bi-layers-fill me-1 text-primary"></i> Semester (Filter 3)
                    </label>
                    <select name="sem" class="form-select filter-select-custom" onchange="this.form.submit()">
                        <option value="all">All Semesters</option>
                        @foreach($semesters as $s)
                            <option value="{{ $s }}" {{ (string)$sem === (string)$s ? 'selected' : '' }}>
                                Semester {{ $s }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter 4: Exam Month/Year --}}
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6">
                    <label class="filter-field-label">
                        <i class="bi bi-calendar3 me-1 text-primary"></i> Exam Month
                    </label>
                    <select name="exam_mon" class="form-select filter-select-custom" onchange="this.form.submit()">
                        <option value="all">All Exam Months</option>
                        @foreach($examMonths as $em)
                            <option value="{{ $em }}" {{ $examMon === $em ? 'selected' : '' }}>
                                {{ $em }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search Box (Search from sketch) --}}
                <div class="col-xl-4 col-lg-6 col-md-8 col-sm-12">
                    <label class="filter-field-label">
                        <i class="bi bi-search me-1 text-primary"></i> Search
                    </label>
                    <div class="input-group">
                        <input type="text"
                               name="q"
                               class="form-control filter-search-input"
                               placeholder="Search Reg No, Name, Subject..."
                               value="{{ $q }}">
                        <button class="btn btn-primary px-3" type="submit" title="Apply Search">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        @if($program !== 'all' || $batch !== 'all' || $sem !== 'all' || $examMon !== 'all' || !empty($q))
                            <a href="{{ route('examinations.examreport', ['tab' => $tab, 'per_page' => $perPage]) }}"
                               class="btn btn-outline-secondary"
                               title="Reset Filters">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Bottom Action Row: Filter summary + CSV & PDF Export (Right aligned as in sketch) --}}
                <div class="col-12 mt-2 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap text-muted small">
                        <span class="active-filter-indicator">
                            <i class="bi bi-funnel-fill text-primary"></i>
                            @if($program === 'all' && $batch === 'all' && $sem === 'all' && $examMon === 'all' && empty($q))
                                Showing all programs & batches
                            @else
                                Filters:
                                <strong>
                                    {{ $program !== 'all' ? 'Prog #' . $program . ' | ' : '' }}
                                    {{ $batch !== 'all' ? str_replace('_', ' ', $batch) . ' | ' : '' }}
                                    {{ $sem !== 'all' ? 'Sem ' . $sem . ' | ' : '' }}
                                    {{ $examMon !== 'all' ? $examMon . ' | ' : '' }}
                                    {{ $q ? '"' . $q . '"' : '' }}
                                </strong>
                            @endif
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border">
                            {{ number_format($records->total()) }} total {{ $tab === 'fail' ? 'failed subject rows' : 'student rows' }}
                        </span>
                    </div>

                    {{-- Export Buttons (CSV and PDF as indicated in wireframe sketch) --}}
                    <div class="d-flex align-items-center gap-2">
                        {{-- CSV Export --}}
                        <a href="{{ route('examinations.examreport.export_csv', request()->query()) }}"
                           class="btn-export-sketch btn-export-csv"
                           title="Download CSV Spreadsheet">
                            <i class="bi bi-file-earmark-spreadsheet-fill me-1 text-success"></i> CSV
                        </a>

                        {{-- PDF / Print Export --}}
                        <button type="button"
                                class="btn-export-sketch btn-export-pdf"
                                onclick="window.print()"
                                title="Print or Save as PDF">
                            <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i> PDF
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- ================= 4. SUMMARY METRIC STRIP ================= --}}
    <div class="row g-2 mb-3">
        <div class="col-xl-3 col-sm-6">
            <div class="summary-metric-card">
                <div class="metric-icon blue"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="metric-label">Total Filtered Students</div>
                    <div class="metric-value">{{ number_format($counts['total_students']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="summary-metric-card">
                <div class="metric-icon red"><i class="bi bi-x-octagon-fill"></i></div>
                <div>
                    <div class="metric-label">Failed Subjects (F Grades)</div>
                    <div class="metric-value text-danger">{{ number_format($counts['failed_subjects']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="summary-metric-card">
                <div class="metric-icon orange"><i class="bi bi-person-exclamation"></i></div>
                <div>
                    <div class="metric-label">Students with Backlogs</div>
                    <div class="metric-value text-warning-emphasis">{{ number_format($counts['students_with_backlogs']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="summary-metric-card">
                <div class="metric-icon gold"><i class="bi bi-star-fill"></i></div>
                <div>
                    <div class="metric-label">High Performers (SGPA ≥ 8.5)</div>
                    <div class="metric-value text-success">{{ number_format($counts['toppers']) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= 5. MAIN DATA CONTAINER ================= --}}
    <div class="sketch-table-card">

        {{-- Table Card Header --}}
        <div class="sketch-table-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                @if($tab === 'fail')
                    <span class="report-badge-pill red">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Backlog & Fail Analysis Report
                    </span>
                @elseif($tab === 'toppers')
                    <span class="report-badge-pill gold">
                        <i class="bi bi-trophy-fill me-1"></i> Top Performers & Merit List
                    </span>
                @else
                    <span class="report-badge-pill blue">
                        <i class="bi bi-calculator-fill me-1"></i> Cumulative Grade Point Average (CGPA) Tabulation
                    </span>
                @endif
                <span class="text-muted small">
                    Page {{ $records->currentPage() }} of {{ $records->lastPage() }}
                </span>
            </div>

            {{-- 200 Rows per page badge explicitly matching sketch --}}
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-2 py-1 small">
                    <i class="bi bi-list-ol me-1 text-primary"></i> <strong>200 rows</strong> per page
                </span>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Rows: {{ $perPage }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item {{ $perPage == 50 ? 'active' : '' }}" href="{{ route('examinations.examreport', array_merge(request()->all(), ['per_page' => 50, 'page' => 1])) }}">50 rows</a></li>
                        <li><a class="dropdown-item {{ $perPage == 100 ? 'active' : '' }}" href="{{ route('examinations.examreport', array_merge(request()->all(), ['per_page' => 100, 'page' => 1])) }}">100 rows</a></li>
                        <li><a class="dropdown-item {{ $perPage == 200 ? 'active' : '' }}" href="{{ route('examinations.examreport', array_merge(request()->all(), ['per_page' => 200, 'page' => 1])) }}">200 rows (Default)</a></li>
                        <li><a class="dropdown-item {{ $perPage == 500 ? 'active' : '' }}" href="{{ route('examinations.examreport', array_merge(request()->all(), ['per_page' => 500, 'page' => 1])) }}">500 rows</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 sketch-report-table">
                <thead>
                    @if($tab === 'fail')
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="min-width: 130px;">Register No</th>
                            <th style="min-width: 180px;">Student Name</th>
                            <th>Program</th>
                            <th>Batch</th>
                            <th class="text-center">Sem</th>
                            <th style="min-width: 110px;">Subject Code</th>
                            <th style="min-width: 220px;">Subject Name</th>
                            <th class="text-center">FA (Int)</th>
                            <th class="text-center">SA (Ext)</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Credits</th>
                            <th class="text-center">Exam Month</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    @elseif($tab === 'toppers')
                        <tr>
                            <th class="text-center" style="width: 70px;">Rank</th>
                            <th style="min-width: 130px;">Register No</th>
                            <th style="min-width: 200px;">Student Name</th>
                            <th>Program</th>
                            <th>Batch</th>
                            <th class="text-center">Sem</th>
                            <th class="text-center" style="min-width: 90px;">SGPA</th>
                            <th class="text-center" style="min-width: 90px;">CGPA</th>
                            <th>Division</th>
                            <th class="text-center">Total Credits</th>
                            <th class="text-center">Subjects</th>
                            <th class="text-center">Exam Month</th>
                            <th class="text-center" style="width: 90px;">Action</th>
                        </tr>
                    @else
                        {{-- CGPA Tab --}}
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="min-width: 130px;">Register No</th>
                            <th style="min-width: 200px;">Student Name</th>
                            <th>Program</th>
                            <th>Batch</th>
                            <th class="text-center">Sem</th>
                            <th class="text-center" style="min-width: 90px;">SGPA</th>
                            <th class="text-center" style="min-width: 90px;">CGPA</th>
                            <th>Division</th>
                            <th class="text-center">Backlogs</th>
                            <th class="text-center">Total Credits</th>
                            <th class="text-center">Exam Month</th>
                            <th class="text-center" style="width: 90px;">Action</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @php
                        $startNumber = ($records->currentPage() - 1) * $records->perPage() + 1;
                    @endphp

                    @forelse($records as $index => $row)
                        @if($tab === 'fail')
                            <tr>
                                <td class="text-center text-muted font-monospace small">
                                    {{ $startNumber + $index }}
                                </td>
                                <td>
                                    <a href="{{ route('examinations.results.memo', ['memo' => $row->regno]) }}"
                                       class="student-regno-link"
                                       title="View Student Memo">
                                        {{ $row->regno }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold student-name-text">{{ $row->student_name ?: '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $row->program_short_name ?: 'N/A' }}
                                    </span>
                                </td>
                                <td class="small text-nowrap">
                                    {{ str_replace('_', ' ', $row->batch ?: '—') }}
                                </td>
                                <td class="text-center font-monospace">
                                    <span class="badge bg-light text-dark border">S{{ $row->s_sem }}</span>
                                </td>
                                <td>
                                    <span class="font-monospace fw-semibold text-secondary">{{ $row->subcode }}</span>
                                </td>
                                <td>
                                    <div class="subject-title-clamp" title="{{ $row->subname }}">
                                        {{ $row->subname ?: '—' }}
                                    </div>
                                </td>
                                <td class="text-center font-monospace">{{ $row->fa_earned ?? '—' }}</td>
                                <td class="text-center font-monospace">{{ $row->sa_earned ?? '—' }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $row->total_earned ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-danger text-white px-2 py-1 font-monospace">
                                        {{ $row->letter_grade ?: 'F' }}
                                    </span>
                                </td>
                                <td class="text-center font-monospace">{{ $row->credits ?? '—' }}</td>
                                <td class="text-center small text-nowrap">{{ $row->exam_mon_yyyy ?: '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('examinations.results.memo', ['memo' => $row->regno]) }}"
                                       class="btn btn-sm btn-outline-primary py-0 px-2"
                                       title="View Marks Memo">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @elseif($tab === 'toppers')
                            @php
                                $rank = $startNumber + $index;
                            @endphp
                            <tr>
                                <td class="text-center">
                                    @if($rank === 1)
                                        <span class="rank-badge rank-1" title="Rank 1">🥇 1</span>
                                    @elseif($rank === 2)
                                        <span class="rank-badge rank-2" title="Rank 2">🥈 2</span>
                                    @elseif($rank === 3)
                                        <span class="rank-badge rank-3" title="Rank 3">🥉 3</span>
                                    @else
                                        <span class="rank-badge rank-general">{{ $rank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('examinations.results.memo', ['memo' => $row->regno]) }}"
                                       class="student-regno-link">
                                        {{ $row->regno }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold student-name-text">{{ $row->student_name ?: '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $row->program_short_name ?: 'N/A' }}
                                    </span>
                                </td>
                                <td class="small text-nowrap">
                                    {{ str_replace('_', ' ', $row->batch ?: '—') }}
                                </td>
                                <td class="text-center font-monospace">
                                    <span class="badge bg-light text-dark border">S{{ $row->s_sem }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fw-bold px-2 py-1 fs-7">
                                        {{ number_format((float)($row->sgpa ?? 0), 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle font-monospace fw-bold px-2 py-1 fs-7">
                                        {{ number_format((float)($row->cgpa ?? 0), 2) }}
                                    </span>
                                </td>
                                <td class="small fw-semibold text-truncate" style="max-width: 180px;">
                                    {{ $row->division_name ?: 'DISTINCTION' }}
                                </td>
                                <td class="text-center font-monospace">{{ $row->total_credits ?? '—' }}</td>
                                <td class="text-center font-monospace">{{ $row->total_subjects ?? '—' }}</td>
                                <td class="text-center small text-nowrap">{{ $row->exam_mon_yyyy ?: '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('examinations.results.memo', ['memo' => $row->regno]) }}"
                                       class="btn btn-sm btn-outline-primary py-0 px-2"
                                       title="View Student Memo">
                                        <i class="bi bi-eye"></i> Memo
                                    </a>
                                </td>
                            </tr>
                        @else
                            {{-- CGPA Report --}}
                            <tr>
                                <td class="text-center text-muted font-monospace small">
                                    {{ $startNumber + $index }}
                                </td>
                                <td>
                                    <a href="{{ route('examinations.results.memo', ['memo' => $row->regno]) }}"
                                       class="student-regno-link">
                                        {{ $row->regno }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold student-name-text">{{ $row->student_name ?: '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $row->program_short_name ?: 'N/A' }}
                                    </span>
                                </td>
                                <td class="small text-nowrap">
                                    {{ str_replace('_', ' ', $row->batch ?: '—') }}
                                </td>
                                <td class="text-center font-monospace">
                                    <span class="badge bg-light text-dark border">S{{ $row->s_sem }}</span>
                                </td>
                                <td class="text-center font-monospace fw-bold text-success">
                                    {{ number_format((float)($row->sgpa ?? 0), 2) }}
                                </td>
                                <td class="text-center font-monospace fw-bold text-primary">
                                    {{ number_format((float)($row->cgpa ?? 0), 2) }}
                                </td>
                                <td class="small fw-semibold text-truncate" style="max-width: 170px;">
                                    {{ $row->division_name ?: '—' }}
                                </td>
                                <td class="text-center font-monospace">
                                    @if(($row->backlogs_count ?? 0) > 0)
                                        <span class="badge bg-danger text-white">
                                            {{ $row->backlogs_count }} Fail
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Clear
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace">{{ $row->total_credits ?? '—' }}</td>
                                <td class="text-center small text-nowrap">{{ $row->exam_mon_yyyy ?: '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('examinations.results.memo', ['memo' => $row->regno]) }}"
                                       class="btn btn-sm btn-outline-primary py-0 px-2"
                                       title="View Student Memo">
                                        <i class="bi bi-eye"></i> Memo
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ $tab === 'fail' ? 15 : ($tab === 'toppers' ? 13 : 13) }}" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-inbox display-5 d-block mb-3 text-secondary opacity-50"></i>
                                    <h6 class="text-dark">No examination records found</h6>
                                    <p class="text-muted small mb-3">No results matched your selected program, batch, semester, or search filter.</p>
                                    <a href="{{ route('examinations.examreport', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ================= 6. PAGINATION FOOTER ================= --}}
        {{-- Matches wireframe sketch: Pagination (200 rows) (1) (2) (3) ... --}}
        <div class="sketch-pagination-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">
                    Showing <strong>{{ $records->firstItem() ?? 0 }}</strong> to <strong>{{ $records->lastItem() ?? 0 }}</strong>
                    of <strong>{{ number_format($records->total()) }}</strong> rows
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">
                        200 rows per page
                    </span>
                </span>
            </div>

            <div class="sketch-pagination-links">
                {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>

</div>

{{-- ================= 7. COMPREHENSIVE STYLES ================= --}}
<style>
    /* Container & Headings */
    .cdoe-examreport-wrapper {
        padding: 4px 0 24px;
        color: var(--text-primary, #1e293b);
    }
    .page-main-heading {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f3b60;
        letter-spacing: -0.02em;
    }
    .heading-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: linear-gradient(135deg, #0f3b60, #1a568c);
        color: #ffffff;
        font-size: 1.1rem;
        box-shadow: 0 2px 6px rgba(15, 59, 96, 0.25);
    }

    /* Sketch Tabs (Matches user drawing) */
    .sketch-tabs-container {
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 8px;
    }
    .sketch-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 0.92rem;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .sketch-tab-btn:hover {
        background: #eef2f6;
        color: #0f3b60;
        border-color: #94a3b8;
    }
    .sketch-tab-btn.active {
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(15, 59, 96, 0.2);
    }
    .sketch-tab-btn.active.tab-fail {
        background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
        border-color: #b91c1c !important;
    }
    .sketch-tab-btn.active.tab-toppers {
        background: linear-gradient(135deg, #d97706, #b45309) !important;
        border-color: #b45309 !important;
    }
    .sketch-tab-btn.active.tab-cgpa {
        background: linear-gradient(135deg, #0f3b60, #1e40af) !important;
        border-color: #1e40af !important;
    }

    .tab-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .tab-indicator.red { background: #ef4444; }
    .tab-indicator.gold { background: #f59e0b; }
    .tab-indicator.blue { background: #3b82f6; }

    .sketch-tab-badge {
        font-size: 0.72rem;
        padding: 2px 7px;
        border-radius: 12px;
        font-weight: 700;
    }

    /* Filter Card */
    .sketch-filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .filter-field-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    .filter-select-custom {
        font-size: 0.85rem;
        border-radius: 6px;
        border-color: #cbd5e1;
        padding: 6px 10px;
        background-color: #f8fafc;
    }
    .filter-select-custom:focus {
        border-color: #0f3b60;
        box-shadow: 0 0 0 3px rgba(15, 59, 96, 0.15);
    }
    .filter-search-input {
        font-size: 0.85rem;
        border-radius: 6px 0 0 6px;
        border-color: #cbd5e1;
    }

    /* Sketch Export Buttons (CSV / PDF in Wireframe) */
    .btn-export-sketch {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid;
        transition: all 0.18s ease;
        background: #ffffff;
    }
    .btn-export-csv {
        border-color: #10b981;
        color: #065f46;
    }
    .btn-export-csv:hover {
        background: #ecfdf5;
        border-color: #059669;
        color: #047857;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.2);
    }
    .btn-export-pdf {
        border-color: #ef4444;
        color: #991b1b;
    }
    .btn-export-pdf:hover {
        background: #fef2f2;
        border-color: #dc2626;
        color: #7f1d1d;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.2);
    }

    /* Metric Strip */
    .summary-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .metric-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .metric-icon.blue { background: #eff6ff; color: #1e40af; }
    .metric-icon.red { background: #fef2f2; color: #dc2626; }
    .metric-icon.orange { background: #fff7ed; color: #ea580c; }
    .metric-icon.gold { background: #fefce8; color: #ca8a04; }
    .metric-label { font-size: 0.75rem; color: #64748b; font-weight: 500; }
    .metric-value { font-size: 1.15rem; font-weight: 700; color: #0f172a; }

    /* Report Table Card */
    .sketch-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .sketch-table-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 16px;
    }
    .report-badge-pill {
        display: inline-flex;
        align-items: center;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .report-badge-pill.red { background: #fee2e2; color: #991b1b; }
    .report-badge-pill.gold { background: #fef3c7; color: #92400e; }
    .report-badge-pill.blue { background: #dbeafe; color: #1e40af; }

    /* Table Typography */
    .sketch-report-table thead th {
        background: #0f3b60;
        color: #ffffff;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 10px 12px;
        border: none;
        white-space: nowrap;
    }
    .sketch-report-table tbody td {
        font-size: 0.83rem;
        padding: 8px 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .student-regno-link {
        font-family: var(--bs-font-monospace);
        font-weight: 700;
        color: #0f3b60;
        text-decoration: none;
    }
    .student-regno-link:hover {
        color: #2563eb;
        text-decoration: underline;
    }
    .student-name-text {
        font-size: 0.84rem;
        color: #0f172a;
    }
    .subject-title-clamp {
        max-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Rank Badges */
    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .rank-badge.rank-1 { background: #fef08a; color: #854d0e; border: 1px solid #facc15; }
    .rank-badge.rank-2 { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }
    .rank-badge.rank-3 { background: #fed7aa; color: #9a3412; border: 1px solid #fb923c; }
    .rank-badge.rank-general { background: #f1f5f9; color: #475569; min-width: 28px; }

    /* Pagination Footer */
    .sketch-pagination-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .sketch-pagination-links .pagination {
        margin-bottom: 0;
        gap: 3px;
    }
    .sketch-pagination-links .page-link {
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f3b60;
        border-radius: 6px !important;
        border-color: #cbd5e1;
        padding: 5px 10px;
    }
    .sketch-pagination-links .page-item.active .page-link {
        background: #0f3b60;
        border-color: #0f3b60;
        color: #ffffff;
    }

    /* Print View */
    @media print {
        header, nav, .sidebar, .sketch-filter-card, .sketch-tabs-container, .btn-export-sketch, .sketch-pagination-footer {
            display: none !important;
        }
        body, .cdoe-examreport-wrapper, .sketch-table-card {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        .sketch-report-table thead th {
            background: #000000 !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact;
        }
        .sketch-report-table td, .sketch-report-table th {
            font-size: 9pt !important;
            padding: 4px 6px !important;
        }
    }
</style>
@endsection

