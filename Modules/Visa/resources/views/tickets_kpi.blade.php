@extends('layouts.app')

@section('page_title', 'Tickets KPI & Performance')

@section('content')
@php
    $period = $period ?? 'all';
    $periodLabels = ['all' => 'All time', 'today' => 'Today', '7' => '7 days', '30' => '30 days', '90' => '90 days'];

    // Build a KPI url that keeps the current period + search
    $kpiUrl = function (array $extra = []) use ($period) {
        $params = array_merge([
            'period' => $period !== 'all' ? $period : null,
            'search' => request('search'),
        ], $extra);

        return route('visa.ticketskpi', array_filter($params, fn ($v) => $v !== null && $v !== '' && $v !== 'all'));
    };

    $c      = $kpi['counts'];
    $rates  = $kpi['rates'];
    $tm     = $kpi['timing'];
    $health = $kpi['health'];
    $sla    = $kpi['sla'];
    $insights = $kpi['insights'];

    $levelMeta = [
        'critical' => ['label' => 'Critical', 'icon' => 'bi-exclamation-octagon-fill'],
        'warning'  => ['label' => 'Warning',  'icon' => 'bi-exclamation-triangle-fill'],
        'info'     => ['label' => 'Insight',  'icon' => 'bi-lightbulb-fill'],
        'good'     => ['label' => 'Good',     'icon' => 'bi-check-circle-fill'],
    ];
    $levelCount = array_count_values(array_column($insights, 'level'));
    $attentionCount = ($levelCount['critical'] ?? 0) + ($levelCount['warning'] ?? 0);
    $actions = array_values(array_filter($insights, fn ($i) => in_array($i['level'], ['critical', 'warning'], true)));

    $statusClasses = [0 => 'status-raised', 1 => 'status-pending', 2 => 'status-in-process', 3 => 'status-resolved'];
    $isActive = fn ($val) => (string) $activeStatus === (string) $val;

    // Data handed to the charts / PDF report
    $kpiJs = [
        'funnel'     => $kpi['funnel'],
        'aging'      => $kpi['aging'],
        'trend'      => $kpi['trend'],
        'hourly'     => $kpi['hourly'],
        'categories' => $kpi['categories'],
        'health'     => $kpi['health'],
        'headline'   => $kpi['headline'],
        'insights'   => $kpi['insights'],
        'counts'     => $kpi['counts'],
        'rates'      => $kpi['rates'],
        'timing'     => $kpi['timing'],
        'sla'        => $kpi['sla'],
        'period'     => $periodLabels[$period],
    ];
    $hasFilter = $activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all';
@endphp

<div class="tickets-container">

    {{-- Flash Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 py-2 px-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div class="small fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-sm ms-auto py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 py-2 px-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div class="small fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close btn-close-sm ms-auto py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ================= TOP LINE: EXECUTIVE SUMMARY ================= --}}
    <div class="exec-line lvl-{{ $health['level'] }}">
        <div class="exec-score" title="Desk health score (0–100)">
            <span class="exec-score-num">{{ $health['score'] ?? '—' }}</span>
            <span class="exec-score-of">/100</span>
        </div>
        <p class="exec-text mb-0">
            <strong>{{ $health['label'] }}.</strong> {{ $kpi['headline'] }}
        </p>
        <span class="exec-updated" title="{{ $periodLabels[$period] }}">
            {{ $periodLabels[$period] }}, updated {{ \Carbon\Carbon::createFromTimestamp($kpi['generated_at'])->setTimezone(config('app.timezone'))->format('h:i A') }}
        </span>
    </div>

    {{-- ================= TWO BUTTONS + PERIOD ================= --}}
    <div class="view-switch-row">
        <div class="view-switch" role="tablist" aria-label="Dashboard view">
            <button type="button" class="view-btn is-active" data-view="kpi" role="tab" aria-selected="true" aria-controls="viewKpi">
                <i class="bi bi-speedometer2"></i> KPI Dashboard
            </button>
            <button type="button" class="view-btn" data-view="ai" role="tab" aria-selected="false" aria-controls="viewAi">
                <i class="bi bi-stars"></i> AI Perceptions
                @if ($attentionCount > 0)
                    <span class="view-btn-count" title="{{ $attentionCount }} findings need attention">{{ $attentionCount }}</span>
                @endif
            </button>
        </div>

        <nav class="period-switch" aria-label="Period">
            @foreach ($periodLabels as $key => $label)
                <a href="{{ $kpiUrl(['period' => $key === 'all' ? null : $key, 'status' => $activeStatus]) }}"
                   class="period-link {{ $period === (string) $key ? 'is-active' : '' }}"
                   @if ($period === (string) $key) aria-current="true" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    {{-- ============================================================
         VIEW 1: KPI DASHBOARD
         ============================================================ --}}
    <section id="viewKpi" class="view-pane" role="tabpanel">

        {{-- 4 status filter pills --}}
        <div class="stat-pills-row mb-3">
            @foreach ([
                ['code' => 0, 'key' => 'raised',     'label' => 'Raised',     'cls' => 'raised',     'id' => 'statValRaised'],
                ['code' => 1, 'key' => 'pending',    'label' => 'Pending',    'cls' => 'pending',    'id' => 'statValPending'],
                ['code' => 2, 'key' => 'in_process', 'label' => 'In Process', 'cls' => 'in-process', 'id' => 'statValInProcess'],
                ['code' => 3, 'key' => 'resolved',   'label' => 'Resolved',   'cls' => 'resolved',   'id' => 'statValResolved'],
            ] as $pill)
                <a href="{{ $kpiUrl(['status' => $isActive($pill['code']) ? null : $pill['code']]) }}" class="stat-pill-link text-decoration-none">
                    <div class="stat-pill stat-pill-{{ $pill['cls'] }} {{ $isActive($pill['code']) ? 'active-filter' : '' }}">
                        <div class="stat-pill-label">
                            <span class="status-indicator-dot dot-{{ $pill['cls'] }}"></span>{{ $pill['label'] }}
                        </div>
                        <div class="stat-pill-val" id="{{ $pill['id'] }}">{{ $stats[$pill['key']] ?? 0 }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="dsm-divider mb-3"></div>

        {{-- Headline rate tiles --}}
        <div class="rate-tiles mb-3">
            <div class="rate-tile">
                <div class="rate-tile-val">{{ $c['total'] }}</div>
                <div class="rate-tile-label">Total tickets</div>
                <div class="rate-tile-sub">{{ $c['open'] }} open, {{ $c['resolved'] }} resolved</div>
            </div>
            <div class="rate-tile">
                <div class="rate-tile-val {{ $rates['resolution'] >= 70 ? 'txt-good' : ($rates['resolution'] >= 40 ? 'txt-warning' : 'txt-critical') }}">{{ $rates['resolution'] }}%</div>
                <div class="rate-tile-label">Resolution rate</div>
                <div class="rate-bar"><span style="width: {{ $rates['resolution'] }}%"></span></div>
            </div>
            <div class="rate-tile">
                <div class="rate-tile-val {{ $rates['acknowledged'] >= 90 ? 'txt-good' : ($rates['acknowledged'] >= 60 ? 'txt-warning' : 'txt-critical') }}">{{ $rates['acknowledged'] }}%</div>
                <div class="rate-tile-label">Opened by staff</div>
                <div class="rate-tile-sub">{{ $c['raised'] }} still waiting to be opened</div>
            </div>
            <div class="rate-tile">
                @if ($rates['sla_resolve'] !== null)
                    <div class="rate-tile-val {{ $rates['sla_resolve'] >= 90 ? 'txt-good' : ($rates['sla_resolve'] >= 70 ? 'txt-warning' : 'txt-critical') }}">{{ $rates['sla_resolve'] }}%</div>
                @else
                    <div class="rate-tile-val txt-muted">—</div>
                @endif
                <div class="rate-tile-label">Resolved within {{ $sla['resolve_hours'] }}h</div>
                <div class="rate-tile-sub">{{ $kpi['breaches']['open_over_resolve_sla'] }} open tickets past target</div>
            </div>
        </div>

        {{-- Ticket journey: average time spent in each stage --}}
        <div class="journey-card mb-3">
            <div class="journey-head">
                <span class="journey-title"><i class="bi bi-signpost-split me-2"></i>Ticket journey: average time at each stage</span>
                <span class="journey-total">
                    Raised to resolved: <strong>{{ $tm['resolution']['avg_fmt'] }}</strong>
                    @if ($tm['resolution']['n'] > 0)
                        <span class="text-muted">(median {{ $tm['resolution']['median_fmt'] }}, slowest 10% over {{ $tm['resolution']['p90_fmt'] }})</span>
                    @endif
                </span>
            </div>
            <div class="journey-track">
                @php
                    $steps = [
                        ['label' => 'Raised',     'cls' => 'raised',     'count' => $c['total']],
                        ['label' => 'Pending',    'cls' => 'pending',    'count' => $c['acknowledged']],
                        ['label' => 'In Process', 'cls' => 'in-process', 'count' => $kpi['funnel']['Reached In Process']],
                        ['label' => 'Resolved',   'cls' => 'resolved',   'count' => $c['resolved']],
                    ];
                    $legs = [
                        ['stat' => $tm['first_response'],     'name' => 'time to open',        'target' => $sla['response_hours'] * 3600],
                        ['stat' => $tm['pending_to_process'], 'name' => 'waiting to start',    'target' => null],
                        ['stat' => $tm['process_to_resolve'], 'name' => 'working to resolve',  'target' => null],
                    ];
                @endphp
                @foreach ($steps as $i => $step)
                    <div class="journey-node node-{{ $step['cls'] }}">
                        <span class="journey-dot"></span>
                        <span class="journey-node-label">{{ $step['label'] }}</span>
                        <span class="journey-node-count">{{ $step['count'] }} {{ $step['count'] === 1 ? 'ticket' : 'tickets' }}</span>
                    </div>
                    @if (isset($legs[$i]))
                        @php $leg = $legs[$i]; $over = $leg['target'] && $leg['stat']['n'] > 0 && $leg['stat']['avg'] > $leg['target']; @endphp
                        <div class="journey-leg {{ $over ? 'is-over' : '' }}">
                            <span class="journey-leg-time">{{ $leg['stat']['avg_fmt'] }}</span>
                            <span class="journey-line"></span>
                            <span class="journey-leg-name">
                                {{ $leg['name'] }}
                                @if ($leg['stat']['n'] > 0)
                                    <span class="text-muted">· {{ $leg['stat']['n'] }} measured</span>
                                @elseif (! $kpi['has_stage_timestamps'])
                                    <span class="text-muted">· needs stage tracking</span>
                                @else
                                    <span class="text-muted">· no data yet</span>
                                @endif
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Charts row 1 --}}
        <div class="kpi-charts-row mb-3">
            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-pie-chart-fill text-primary me-2"></i><span>Status distribution</span>
                </div>
                <div class="kpi-chart-body donut-wrap">
                    <canvas id="statusDonutChart" height="220"></canvas>
                    <div class="donut-legend">
                        <div class="donut-legend-item"><span><span class="donut-dot" style="background:#d97706"></span> Raised</span><strong>{{ $stats['raised'] }}</strong></div>
                        <div class="donut-legend-item"><span><span class="donut-dot" style="background:#0284c7"></span> Pending</span><strong>{{ $stats['pending'] }}</strong></div>
                        <div class="donut-legend-item"><span><span class="donut-dot" style="background:#7c3aed"></span> In Process</span><strong>{{ $stats['in_process'] }}</strong></div>
                        <div class="donut-legend-item"><span><span class="donut-dot" style="background:#16a34a"></span> Resolved</span><strong>{{ $stats['resolved'] }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-funnel-fill text-indigo me-2"></i><span>Lifecycle funnel</span>
                </div>
                <div class="kpi-chart-body"><div class="chart-box"><canvas id="funnelChart"></canvas></div></div>
            </div>

            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-hourglass-split text-warning me-2"></i><span>Open tickets by age</span>
                    <span class="chart-note">target {{ $sla['resolve_hours'] }}h</span>
                </div>
                <div class="kpi-chart-body"><div class="chart-box"><canvas id="agingChart"></canvas></div></div>
            </div>
        </div>

        {{-- Charts row 2 --}}
        <div class="kpi-charts-row kpi-charts-row-wide mb-3">
            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-graph-up text-success me-2"></i><span>Raised vs resolved, last 14 days</span>
                    @php $w = $kpi['wow']; @endphp
                    <span class="chart-note">
                        This week: {{ $w['raised_this'] }} raised
                        @if ($w['raised_change'] !== null)
                            <span class="{{ $w['raised_change'] > 0 ? 'txt-warning' : 'txt-good' }}">({{ $w['raised_change'] > 0 ? '+' : '' }}{{ $w['raised_change'] }}% vs last week)</span>
                        @endif
                        and {{ $w['resolved_this'] }} resolved
                    </span>
                </div>
                <div class="kpi-chart-body"><div class="chart-box"><canvas id="trendChart"></canvas></div></div>
            </div>

            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-clock-fill text-primary me-2"></i><span>When tickets arrive</span>
                </div>
                <div class="kpi-chart-body"><div class="chart-box"><canvas id="hourlyChart"></canvas></div></div>
            </div>
        </div>

        {{-- Charts row 3 --}}
        <div class="kpi-charts-row mb-3">
            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-tags-fill text-indigo me-2"></i><span>Tickets by category</span>
                </div>
                <div class="kpi-chart-body"><div class="chart-box"><canvas id="categoryChart"></canvas></div></div>
            </div>

            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-people-fill text-primary me-2"></i><span>Staff performance</span>
                </div>
                <div class="kpi-chart-body p-0 justify-content-start">
                    @if (count($kpi['staff']))
                        <div class="table-responsive-dsm">
                            <table class="mini-table">
                                <thead>
                                    <tr><th>Staff</th><th class="text-end">Handled</th><th class="text-end">Resolved</th><th class="text-end">Avg time</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($kpi['staff'] as $s)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark text-truncate" style="max-width:150px" title="{{ $s['name'] }}">{{ $s['name'] }}</div>
                                                <div class="text-muted smaller">{{ $s['code'] }} · {{ $s['open'] }} open</div>
                                            </td>
                                            <td class="text-end fw-bold">{{ $s['handled'] }}</td>
                                            <td class="text-end">{{ $s['resolved'] }} <span class="text-muted smaller">({{ $s['rate'] }}%)</span></td>
                                            <td class="text-end">{{ $s['avg_res'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-note">No ticket has been picked up by staff in this period.</div>
                    @endif
                </div>
            </div>

            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-eye-fill text-danger me-2"></i><span>Watchlist: oldest open tickets</span>
                </div>
                <div class="kpi-chart-body p-0 justify-content-start">
                    @forelse ($kpi['oldest_open'] as $o)
                        <button type="button" class="watch-row" onclick="openViewModal('{{ $o['tkt_id'] }}')">
                            <span class="watch-main">
                                <span class="tkt-code">{{ $o['tkt_id'] }}</span>
                                <span class="watch-meta">{{ $o['regno'] }} · {{ $o['category'] }}</span>
                            </span>
                            <span class="watch-side">
                                <span class="status-chip {{ $statusClasses[$o['status']] ?? '' }}">{{ $o['status_label'] }}</span>
                                <span class="watch-age {{ $o['breached'] ? 'txt-critical' : '' }}">{{ $o['age'] }}</span>
                            </span>
                        </button>
                    @empty
                        <div class="empty-note">No open tickets. Everything is resolved.</div>
                    @endforelse

                    @if (count($kpi['repeat_students']))
                        <div class="watch-sub">Students with repeat tickets</div>
                        <div class="repeat-list">
                            @foreach ($kpi['repeat_students'] as $rs)
                                <a href="{{ $kpiUrl(['search' => $rs['regno']]) }}" class="repeat-chip" title="Show tickets for {{ $rs['regno'] }}">
                                    {{ $rs['regno'] }} <strong>{{ $rs['count'] }}</strong>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================= TICKETS TABLE ================= --}}
        <div class="sample-rect-box">
            <div class="sample-rect-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="sample-rect-title">Resolution & Response Metrics</span>
                    @if ($hasFilter)
                        <span class="badge bg-secondary text-uppercase fw-normal small">
                            Filter: {{ \Modules\Visa\Models\TicketDetail::STATUS_LABELS[(int)$activeStatus] ?? 'All' }}
                        </span>
                        <a href="{{ $kpiUrl() }}" class="btn btn-sm btn-link text-decoration-none p-0 text-muted small">
                            <i class="bi bi-x-circle"></i> Clear Filter
                        </a>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form action="{{ route('visa.ticketskpi') }}" method="GET" class="d-flex align-items-center">
                        @if ($hasFilter)
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        @if ($period !== 'all')
                            <input type="hidden" name="period" value="{{ $period }}">
                        @endif
                        <div class="input-group input-group-sm dsm-search-input">
                            <input type="text" name="search" class="form-control" placeholder="Search ticket, regno..." value="{{ request('search') }}" aria-label="Search tickets">
                            <button class="btn btn-outline-secondary" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                    <span class="sample-rect-badge">{{ $tickets->total() }} records</span>

                    <div class="dropdown" id="downloadDropdownKpi">
                        <button class="btn-download-tbl" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Download table data">
                            <i class="bi bi-download me-1"></i> Download
                            <i class="bi bi-chevron-down ms-1" style="font-size:10px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 download-menu" style="min-width:150px;">
                            <li>
                                <button class="dropdown-item download-opt" onclick="exportTablePDF('kpiTicketsTable','Resolution_Response_Metrics')" type="button">
                                    <span class="download-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                    PDF
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item download-opt" onclick="exportTableExcel('kpiTicketsTable','Resolution_Response_Metrics')" type="button">
                                    <span class="download-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-file-earmark-spreadsheet-fill"></i></span>
                                    Excel (.xlsx)
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item download-opt" onclick="exportTableCSV('kpiTicketsTable','Resolution_Response_Metrics')" type="button">
                                    <span class="download-icon" style="background:#e0f2fe;color:#0284c7;"><i class="bi bi-filetype-csv"></i></span>
                                    CSV
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="sample-rect-body p-0">
                @if ($tickets->isNotEmpty())
                    <div class="table-responsive-dsm">
                        <table class="table sample-table align-middle mb-0" id="kpiTicketsTable">
                            <thead>
                                <tr>
                                    <th style="width: 125px;">TICKET ID</th>
                                    <th>STUDENT / REG NO</th>
                                    <th>EMPLOYEE</th>
                                    <th style="width: 110px;">RAISED</th>
                                    <th style="width: 110px;">SOLVED</th>
                                    <th style="width: 120px;">TIME TAKEN</th>
                                    <th style="width: 95px;">STATUS</th>
                                    <th style="width: 65px;" class="text-center">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $tkt)
                                    @php
                                        $openAgeSec = (! $tkt->is_resolved && $tkt->tkt_raised_datetime)
                                            ? \Carbon\Carbon::parse($tkt->tkt_raised_datetime)->diffInSeconds(now(), true)
                                            : 0;
                                        $pastTarget = $openAgeSec > $sla['resolve_hours'] * 3600;
                                    @endphp
                                    <tr class="clickable-ticket-row" onclick="openViewModal('{{ $tkt->tkt_id }}')" title="Click to view ticket details">
                                        <td>
                                            <span class="tkt-code">{{ $tkt->tkt_id }}</span>
                                            <div class="smaller text-muted text-truncate mt-1" style="max-width: 115px;" title="{{ $tkt->ticketMaster?->tkt_main_type }}">
                                                {{ $tkt->ticketMaster?->tkt_main_type ?? 'Visa' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark small text-truncate" style="max-width: 140px;">{{ $tkt->regno }}</div>
                                            <div class="text-muted smaller text-truncate" style="max-width: 140px;" title="{{ $tkt->student?->student_name ?? $tkt->student?->name }}">
                                                {{ $tkt->student?->student_name ?? $tkt->student?->name ?? 'Student' }}
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $empCode = $tkt->emp_userid ?: ($tkt->assigned_empcode ?: null);
                                                $empName = $tkt->handlerEmployee?->full_name ?? ($tkt->assignedEmployee?->full_name ?? null);
                                            @endphp
                                            @if ($empCode)
                                                <div class="fw-semibold text-dark small text-truncate d-flex align-items-center gap-1" style="max-width: 135px;">
                                                    <i class="bi bi-person-badge text-primary"></i> {{ $empCode }}
                                                </div>
                                                <div class="text-muted smaller text-truncate" style="max-width: 135px;" title="{{ $empName ?? 'Staff' }}">
                                                    {{ $empName ?: 'Support Staff' }}
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border fw-normal smaller py-0 px-1">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-muted">
                                            @if ($tkt->tkt_raised_datetime)
                                                <div class="fw-medium text-dark smaller">{{ \Carbon\Carbon::parse($tkt->tkt_raised_datetime)->format('d M Y') }}</div>
                                                <div class="smaller text-muted">{{ \Carbon\Carbon::parse($tkt->tkt_raised_datetime)->format('h:i A') }}</div>
                                            @else
                                                <span>—</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap text-muted">
                                            @if ($tkt->is_resolved && $tkt->emp_timestamp)
                                                <div class="fw-medium text-dark smaller">{{ \Carbon\Carbon::parse($tkt->emp_timestamp)->format('d M Y') }}</div>
                                                <div class="smaller text-success">{{ \Carbon\Carbon::parse($tkt->emp_timestamp)->format('h:i A') }}</div>
                                            @elseif ($tkt->is_resolved)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle smaller py-0 px-1">Resolved</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle smaller py-0 px-1">Open</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($tkt->is_resolved)
                                                <span class="duration-badge duration-badge-resolved py-0 px-2 fw-semibold" title="Solved in {{ $tkt->duration_formatted }}">
                                                    <i class="bi bi-check2-circle me-1 text-success"></i>{{ $tkt->duration_formatted }}
                                                </span>
                                            @else
                                                <span class="duration-badge {{ $pastTarget ? 'duration-badge-breach' : 'duration-badge-pending' }} py-0 px-2 fw-semibold"
                                                      title="Open for {{ $tkt->duration_formatted }}{{ $pastTarget ? ', past the '.$sla['resolve_hours'].'h target' : '' }}">
                                                    <i class="bi {{ $pastTarget ? 'bi-alarm' : 'bi-clock-history' }} me-1"></i>{{ $tkt->duration_formatted }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-chip {{ $tkt->status_class }} py-0 px-2 smaller">
                                                {{ $tkt->status_label }}
                                            </span>
                                        </td>
                                        <td class="text-center" onclick="event.stopPropagation();">
                                            <button type="button" class="btn-action-view" onclick="openViewModal('{{ $tkt->tkt_id }}')" title="View ticket details">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 border-top flex-wrap gap-2">
                        <span class="text-muted small">Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} tickets</span>
                        <div>{{ $tickets->links() }}</div>
                    </div>
                @else
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        <p class="mb-0">No tickets match this filter. Clear the filter or choose a wider period.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============================================================
         VIEW 2: AI PERCEPTIONS
         ============================================================ --}}
    <section id="viewAi" class="view-pane d-none" role="tabpanel">

        <div class="ai-top mb-3">
            {{-- Health score --}}
            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-heart-pulse-fill text-danger me-2"></i><span>Desk health score</span>
                </div>
                <div class="kpi-chart-body health-body">
                    <div class="gauge-wrap">
                        <canvas id="healthGauge"></canvas>
                        <div class="gauge-center">
                            <div class="gauge-num txt-{{ $health['level'] }}">{{ $health['score'] ?? '—' }}</div>
                            <div class="gauge-label">{{ $health['label'] }}</div>
                        </div>
                    </div>
                    <div class="health-parts">
                        @foreach ($health['parts'] as $part)
                            <div class="health-part">
                                <div class="health-part-head">
                                    <span>{{ $part['label'] }}</span>
                                    <span><strong>{{ $part['value'] }}%</strong> <span class="text-muted">· weight {{ $part['weight'] }}</span></span>
                                </div>
                                <div class="rate-bar"><span style="width: {{ $part['value'] }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recommended actions --}}
            <div class="kpi-chart-card">
                <div class="kpi-chart-card-header">
                    <i class="bi bi-list-check text-success me-2"></i><span>Recommended actions, in priority order</span>
                    <button type="button" class="btn-download-tbl ms-auto" onclick="exportAiReportPDF()">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Download director report
                    </button>
                </div>
                <div class="kpi-chart-body justify-content-start">
                    @if (count($actions))
                        <ol class="action-list">
                            @foreach ($actions as $a)
                                <li class="lvl-{{ $a['level'] }}">
                                    <span class="action-text">{{ $a['action'] }}</span>
                                    <span class="action-why">{{ $a['title'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <div class="empty-note">No urgent actions. The desk is running within targets.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Perception filter --}}
        <div class="ai-filter mb-2" role="group" aria-label="Filter findings">
            <button type="button" class="ai-filter-btn is-active" data-level="all">All <span>{{ count($insights) }}</span></button>
            @foreach ($levelMeta as $lvl => $meta)
                @if (! empty($levelCount[$lvl]))
                    <button type="button" class="ai-filter-btn lvl-{{ $lvl }}" data-level="{{ $lvl }}">{{ $meta['label'] }} <span>{{ $levelCount[$lvl] }}</span></button>
                @endif
            @endforeach
        </div>

        {{-- Perception cards --}}
        <div class="insight-grid">
            @foreach ($insights as $ins)
                <article class="insight-card lvl-{{ $ins['level'] }}" data-level="{{ $ins['level'] }}">
                    <div class="insight-head">
                        <span class="insight-icon"><i class="bi {{ $ins['icon'] }}"></i></span>
                        <span class="insight-tag">{{ $levelMeta[$ins['level']]['label'] }}</span>
                    </div>
                    <h3 class="insight-title">{{ $ins['title'] }}</h3>
                    <p class="insight-text">{{ $ins['text'] }}</p>
                    <p class="insight-action"><i class="bi bi-arrow-return-right me-1"></i>{{ $ins['action'] }}</p>
                </article>
            @endforeach
        </div>

        <p class="ai-footnote">
            Findings are generated automatically from ticket data in the selected period ({{ strtolower($periodLabels[$period]) }}).
            Targets used: open a ticket within {{ $sla['response_hours'] }} hours, resolve within {{ $sla['resolve_hours'] }} hours.
            @unless ($kpi['has_stage_timestamps'])
                Stage timings are estimated until stage tracking is enabled.
            @endunless
        </p>
    </section>

</div>

{{-- ================= MODAL: VIEW TICKET DETAILS ================= --}}
<div class="modal fade" id="viewTicketModal" tabindex="-1" aria-labelledby="viewTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-ticket-detailed-fill text-primary fs-5"></i>
                    <h5 class="modal-title fs-6 fw-bold text-dark mb-0" id="viewTicketModalLabel">
                        Ticket View: <span id="modalTktId" class="text-primary font-monospace"></span>
                    </h5>
                    <span id="modalStatusBadge" class="status-chip ms-2"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                {{-- Quick Summary Card --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-light-subtle h-100">
                            <div class="text-muted smaller fw-bold text-uppercase mb-1">Student Details</div>
                            <div class="fw-bold text-dark small" id="modalStudentName">—</div>
                            <div class="text-muted smaller">Register No: <span id="modalRegNo" class="font-monospace text-dark fw-semibold">—</span></div>
                            <div class="text-muted smaller">Program: <span id="modalProgram" class="text-dark">—</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 border rounded bg-light-subtle h-100">
                            <div class="text-muted smaller fw-bold text-uppercase mb-1">Handling Staff</div>
                            <div class="fw-bold text-dark small" id="modalEmpName">—</div>
                            <div class="text-muted smaller">Staff Code: <span id="modalEmpCode" class="font-monospace text-dark fw-semibold">—</span></div>
                            <div class="text-muted smaller">Turnaround Duration: <span id="modalDuration" class="fw-bold text-success">—</span></div>
                        </div>
                    </div>
                </div>

                {{-- Time Difference & SLA Box --}}
                <div class="p-3 mb-3 rounded-2 border bg-light">
                    <div class="row text-center g-2">
                        <div class="col-4">
                            <div class="smaller text-muted text-uppercase fw-semibold">Raised At</div>
                            <div class="small fw-bold text-dark mt-1" id="modalRaisedDate">—</div>
                        </div>
                        <div class="col-4 border-start border-end">
                            <div class="smaller text-muted text-uppercase fw-semibold">Resolved At</div>
                            <div class="small fw-bold text-dark mt-1" id="modalResolvedDate">—</div>
                        </div>
                        <div class="col-4">
                            <div class="smaller text-muted text-uppercase fw-semibold">Time to Solve</div>
                            <div class="small fw-bold text-primary mt-1" id="modalTimeDifference">—</div>
                        </div>
                    </div>
                </div>

                {{-- Issue / Query Description --}}
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted smaller fw-bold text-uppercase">Ticket Query & Category</span>
                        <span class="badge bg-light text-dark border smaller" id="modalCategoryBadge"></span>
                    </div>
                    <div class="p-3 bg-white border rounded small text-dark" id="modalQueryText" style="min-height: 70px; white-space: pre-wrap;"></div>
                </div>

                {{-- Resolution Remarks (If Handled) --}}
                <div id="modalResolutionSection" class="p-3 rounded-2 border bg-success-subtle mb-2 d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-bold text-success smaller text-uppercase d-flex align-items-center gap-1">
                            <i class="bi bi-check-circle-fill"></i> Staff Resolution Remarks & Solution
                        </div>
                        <span class="badge bg-success text-white smaller px-2 py-1">
                            <i class="bi bi-shield-check me-1"></i>Official Solution
                        </span>
                    </div>

                    {{-- Dedicated White Solution Card for Crystal Clear Reading --}}
                    <div class="p-3 bg-white border rounded text-dark small" id="modalResolutionComment" style="min-height: 55px; white-space: pre-wrap; line-height: 1.55; border-left: 3.5px solid #16a34a !important;"></div>

                    {{-- Attached File Download --}}
                    <div id="modalAttachmentWrap" class="d-none mt-2 pt-2 border-top border-success-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="smaller text-muted fw-semibold">
                            <i class="bi bi-paperclip me-1"></i>Attached Solution Document:
                        </span>
                        <a href="#" id="modalAttachmentLink" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 px-3 py-1 fw-semibold">
                            <i class="bi bi-download"></i> <span id="modalAttachmentName">Download File</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-end">
                <button type="button" class="btn btn-sm btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ================= STYLING ================= --}}
<style>
    .tickets-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 6px 8px 28px;
    }

    /* 1. TOP ROW: 4 SMALL COMPACT PILLS */
    .stat-pills-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .stat-pill {
        background: var(--bg-card, #ffffff);
        border: 1.5px solid #000000;
        border-radius: 12px;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
    }
    .stat-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }
    .stat-pill.active-filter {
        border-width: 2.5px;
        box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.15);
    }
    .stat-pill-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.2px;
    }
    .stat-pill-val {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        font-family: inherit;
        line-height: 1;
    }

    .status-indicator-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .dot-raised { background: #d97706; }
    .dot-pending { background: #0284c7; }
    .dot-in-process { background: #7c3aed; }
    .dot-resolved { background: #16a34a; }

    .stat-pill-raised { border-color: #d97706 !important; }
    .stat-pill-pending { border-color: #0284c7 !important; }
    .stat-pill-in-process { border-color: #7c3aed !important; }
    .stat-pill-resolved { border-color: #16a34a !important; }

    .stat-pill-raised .stat-pill-val { color: #d97706; }
    .stat-pill-pending .stat-pill-val { color: #0284c7; }
    .stat-pill-in-process .stat-pill-val { color: #7c3aed; }
    .stat-pill-resolved .stat-pill-val { color: #16a34a; }

    /* DIVIDER */
    .dsm-divider {
        height: 1.5px;
        background: #000000;
        width: 100%;
        opacity: 0.85;
    }

    /* SAMPLE RECTANGLE BOX */
    .sample-rect-box {
        background: #ffffff;
        border: 1.5px solid #000000;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }
    .sample-rect-header {
        background: #ffffff;
        border-bottom: 1.5px solid #000000;
        padding: 10px 16px;
    }
    .sample-rect-title {
        font-size: 13.5px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.2px;
    }
    .sample-rect-badge {
        background: #f1f5f9;
        color: #475569;
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }

    .dsm-search-input {
        width: 220px;
    }

    /* TABLE */
    .table-responsive-dsm {
        overflow-x: auto;
        width: 100%;
    }
    .sample-table {
        width: 100%;
        font-size: 12px;
        border-collapse: separate;
        border-spacing: 0;
    }
    .sample-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        border-bottom: 1.5px solid #000000;
        padding: 8px 10px;
        white-space: nowrap;
    }
    .sample-table tbody td {
        padding: 7px 10px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    /* CLICKABLE ROW */
    .clickable-ticket-row {
        cursor: pointer;
        transition: background-color 0.12s ease;
    }
    .clickable-ticket-row:hover {
        background-color: #f1f5f9 !important;
    }

    .tkt-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        font-weight: 800;
        color: #0284c7;
        background: #e0f2fe;
        padding: 2px 7px;
        border-radius: 6px;
        border: 1px solid #bae6fd;
        display: inline-block;
    }

    /* DURATION BADGES */
    .duration-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
    }
    .duration-badge-resolved {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .duration-badge-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    /* STATUS CHIP */
    .status-chip {
        display: inline-flex;
        align-items: center;
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        white-space: nowrap;
    }
    .status-raised {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .status-pending {
        background: #e0f2fe;
        color: #075985;
        border: 1px solid #bae6fd;
    }
    .status-in-process {
        background: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #e9d5ff;
    }
    .status-resolved {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .btn-action-view {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        color: #0f172a;
        border-radius: 6px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-action-view:hover {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
    }

    @media (max-width: 991px) {
        .stat-pills-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 575px) {
        .stat-pills-row {
            grid-template-columns: 1fr;
        }
        .dsm-search-input {
            width: 100%;
        }
    }

    /* ====== CHARTS ROW ====== */
    .kpi-charts-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
    }
    .kpi-chart-card {
        background: #ffffff;
        border: 1.5px solid #000000;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
    }
    .kpi-chart-card-header {
        background: #f8fafc;
        border-bottom: 1.5px solid #000000;
        padding: 9px 14px;
        font-size: 12.5px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.2px;
        display: flex;
        align-items: center;
    }
    .kpi-chart-body {
        padding: 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .donut-wrap {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 14px;
    }
    .donut-wrap canvas {
        flex-shrink: 0;
        max-width: 110px;
        max-height: 110px;
    }
    .donut-legend {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .donut-legend-item {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        color: #334155;
        justify-content: space-between;
    }
    .donut-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    /* KPI Metrics Grid */
    .kpi-metrics-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 10px 14px 14px;
    }
    .kpi-metric-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: transform 0.15s ease;
    }
    .kpi-metric-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.06);
    }
    .kpi-metric-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .kpi-metric-val {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }
    .kpi-metric-label {
        font-size: 10.5px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-top: 1px;
    }

    @media (max-width: 991px) {
        .kpi-charts-row {
            grid-template-columns: 1fr 1fr;
        }
        .kpi-chart-card:last-child {
            grid-column: 1 / -1;
        }
    }
    @media (max-width: 575px) {
        .kpi-charts-row {
            grid-template-columns: 1fr;
        }
        .kpi-chart-card:last-child {
            grid-column: auto;
        }
        .donut-wrap {
            flex-direction: column;
        }
    }

    /* ===== DOWNLOAD BUTTON ===== */
    .btn-download-tbl {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #0f172a;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.12s ease;
        white-space: nowrap;
    }
    .btn-download-tbl:hover {
        background: #1e40af;
        transform: translateY(-1px);
    }
    .download-menu {
        border-radius: 10px !important;
        padding: 6px !important;
    }
    .download-opt {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12.5px;
        font-weight: 600;
        color: #0f172a;
        border-radius: 7px;
        padding: 6px 10px;
        transition: background 0.12s ease;
    }
    .download-opt:hover { background: #f1f5f9; }
    .download-icon {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* =====================================================
       KPI + AI PERCEPTIONS ADDITIONS
       ===================================================== */
    .txt-good { color: #16a34a !important; }
    .txt-warning { color: #d97706 !important; }
    .txt-critical { color: #dc2626 !important; }
    .txt-info { color: #0284c7 !important; }
    .txt-muted { color: #94a3b8 !important; }

    /* TOP LINE */
    .exec-line {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #ffffff;
        border: 1.5px solid #000000;
        border-left-width: 6px;
        border-radius: 12px;
        padding: 10px 16px;
        margin-bottom: 12px;
    }
    .exec-line.lvl-good { border-left-color: #16a34a; }
    .exec-line.lvl-info { border-left-color: #0284c7; }
    .exec-line.lvl-warning { border-left-color: #d97706; }
    .exec-line.lvl-critical { border-left-color: #dc2626; }
    .exec-score { display: flex; align-items: baseline; flex-shrink: 0; }
    .exec-score-num { font-size: 28px; font-weight: 800; line-height: 1; color: #0f172a; font-variant-numeric: tabular-nums; }
    .lvl-good .exec-score-num { color: #16a34a; }
    .lvl-info .exec-score-num { color: #0284c7; }
    .lvl-warning .exec-score-num { color: #d97706; }
    .lvl-critical .exec-score-num { color: #dc2626; }
    .exec-score-of { font-size: 12px; font-weight: 700; color: #64748b; margin-left: 2px; }
    .exec-text { flex: 1; font-size: 13.5px; color: #0f172a; line-height: 1.45; }
    .exec-updated { font-size: 11.5px; color: #64748b; white-space: nowrap; }

    /* TWO BUTTONS + PERIOD */
    .view-switch-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .view-switch { display: flex; gap: 8px; }
    .view-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #ffffff;
        color: #0f172a;
        border: 1.5px solid #000000;
        border-radius: 10px;
        padding: 8px 18px;
        font-size: 13.5px;
        font-weight: 800;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .view-btn:hover { background: #f1f5f9; }
    .view-btn.is-active { background: #0f172a; color: #ffffff; }
    .view-btn:focus-visible, .period-link:focus-visible, .ai-filter-btn:focus-visible, .watch-row:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }
    .view-btn-count {
        background: #dc2626;
        color: #ffffff;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .period-switch {
        display: flex;
        border: 1.5px solid #000000;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }
    .period-link {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        padding: 7px 12px;
        text-decoration: none;
        border-left: 1px solid #e2e8f0;
    }
    .period-link:first-child { border-left: none; }
    .period-link:hover { background: #f1f5f9; color: #0f172a; }
    .period-link.is-active { background: #0f172a; color: #ffffff; }

    /* RATE TILES */
    .rate-tiles {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .rate-tile {
        background: #ffffff;
        border: 1.5px solid #000000;
        border-radius: 12px;
        padding: 12px 14px;
    }
    .rate-tile-val { font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1.05; font-variant-numeric: tabular-nums; }
    .rate-tile-label { font-size: 12.5px; font-weight: 700; color: #334155; margin-top: 2px; }
    .rate-tile-sub { font-size: 11.5px; color: #64748b; margin-top: 6px; }
    .rate-bar { height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-top: 8px; }
    .rate-bar span { display: block; height: 100%; background: #0f172a; border-radius: 999px; }

    /* JOURNEY */
    .journey-card {
        background: #ffffff;
        border: 1.5px solid #000000;
        border-radius: 12px;
        overflow: hidden;
    }
    .journey-head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
        background: #f8fafc;
        border-bottom: 1.5px solid #000000;
        padding: 9px 14px;
    }
    .journey-title { font-size: 12.5px; font-weight: 800; color: #0f172a; }
    .journey-total { font-size: 12.5px; color: #0f172a; }
    .journey-track {
        display: flex;
        align-items: flex-start;
        padding: 18px 18px 14px;
        overflow-x: auto;
    }
    .journey-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 92px;
        flex-shrink: 0;
        text-align: center;
    }
    .journey-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid currentColor;
        background: #ffffff;
        margin-bottom: 6px;
    }
    .node-raised { color: #d97706; }
    .node-pending { color: #0284c7; }
    .node-in-process { color: #7c3aed; }
    .node-resolved { color: #16a34a; }
    .node-resolved .journey-dot { background: currentColor; }
    .journey-node-label { font-size: 12.5px; font-weight: 800; color: #0f172a; }
    .journey-node-count { font-size: 11px; color: #64748b; }
    .journey-leg {
        flex: 1;
        min-width: 140px;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: -6px;
    }
    .journey-leg-time { font-size: 18px; font-weight: 800; color: #0f172a; font-variant-numeric: tabular-nums; }
    .journey-leg.is-over .journey-leg-time { color: #dc2626; }
    .journey-line {
        width: 100%;
        height: 0;
        border-top: 2px dashed #94a3b8;
        margin: 2px 0 4px;
    }
    .journey-leg.is-over .journey-line { border-top-color: #dc2626; }
    .journey-leg-name { font-size: 11px; color: #334155; text-align: center; }

    /* CHART CARDS (extends the original) */
    .kpi-chart-card-header .chart-note {
        margin-left: auto;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
    }
    .kpi-charts-row-wide { grid-template-columns: 2fr 1fr; }
    .chart-box { position: relative; height: 220px; width: 100%; }

    .mini-table { width: 100%; font-size: 12px; border-collapse: collapse; }
    .mini-table th {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        padding: 8px 12px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .mini-table td { padding: 7px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .mini-table tr:last-child td { border-bottom: none; }

    .watch-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        width: 100%;
        background: #ffffff;
        border: none;
        border-bottom: 1px solid #f1f5f9;
        padding: 8px 12px;
        text-align: left;
    }
    .watch-row:hover { background: #f8fafc; }
    .watch-main { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .watch-meta { font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px; }
    .watch-side { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; }
    .watch-age { font-size: 12px; font-weight: 800; color: #0f172a; }
    .watch-sub { font-size: 11px; font-weight: 700; color: #64748b; padding: 10px 12px 4px; }
    .repeat-list { display: flex; flex-wrap: wrap; gap: 6px; padding: 0 12px 12px; }
    .repeat-chip {
        font-size: 11.5px;
        color: #0f172a;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 2px 8px;
        text-decoration: none;
    }
    .repeat-chip:hover { background: #e2e8f0; }
    .empty-note { padding: 18px 14px; font-size: 12.5px; color: #64748b; }
    .duration-badge-breach { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* AI PERCEPTIONS */
    .ai-top { display: grid; grid-template-columns: 1fr 1.3fr; gap: 12px; }
    .health-body { flex-direction: row; align-items: center; gap: 18px; }
    .gauge-wrap { position: relative; width: 190px; height: 110px; flex-shrink: 0; }
    .gauge-center { position: absolute; left: 0; right: 0; bottom: 4px; text-align: center; }
    .gauge-num { font-size: 32px; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
    .gauge-label { font-size: 12px; font-weight: 700; color: #334155; }
    .health-parts { flex: 1; display: flex; flex-direction: column; gap: 10px; }
    .health-part-head { display: flex; justify-content: space-between; font-size: 12px; color: #334155; }
    .health-part .rate-bar { margin-top: 4px; }

    .action-list { margin: 0; padding-left: 0; list-style: none; counter-reset: act; }
    .action-list li {
        counter-increment: act;
        position: relative;
        padding: 8px 0 8px 36px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
    }
    .action-list li:last-child { border-bottom: none; }
    .action-list li::before {
        content: counter(act);
        position: absolute;
        left: 0;
        top: 8px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        background: #d97706;
    }
    .action-list li.lvl-critical::before { background: #dc2626; }
    .action-text { font-size: 13px; font-weight: 700; color: #0f172a; }
    .action-why { font-size: 11.5px; color: #64748b; }

    .ai-filter { display: flex; flex-wrap: wrap; gap: 6px; }
    .ai-filter-btn {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
    }
    .ai-filter-btn span { color: #64748b; margin-left: 3px; }
    .ai-filter-btn.is-active { background: #0f172a; border-color: #0f172a; color: #ffffff; }
    .ai-filter-btn.is-active span { color: #cbd5e1; }

    .insight-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .insight-card {
        --lvl: #0284c7;
        --lvl-bg: #f0f9ff;
        background: #ffffff;
        border: 1.5px solid #000000;
        border-left: 6px solid var(--lvl);
        border-radius: 12px;
        padding: 12px 16px;
    }
    .lvl-critical { --lvl: #dc2626; --lvl-bg: #fef2f2; }
    .lvl-warning { --lvl: #d97706; --lvl-bg: #fffbeb; }
    .lvl-info { --lvl: #0284c7; --lvl-bg: #f0f9ff; }
    .lvl-good { --lvl: #16a34a; --lvl-bg: #f0fdf4; }
    .insight-head { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
    .insight-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--lvl-bg);
        color: var(--lvl);
        font-size: 14px;
    }
    .insight-tag { font-size: 11.5px; font-weight: 800; color: var(--lvl); }
    .insight-title { font-size: 14.5px; font-weight: 800; color: #0f172a; margin: 0 0 4px; line-height: 1.3; }
    .insight-text { font-size: 12.5px; color: #334155; margin: 0 0 8px; line-height: 1.5; }
    .insight-action {
        font-size: 12.5px;
        font-weight: 600;
        color: #0f172a;
        background: var(--lvl-bg);
        border-radius: 8px;
        padding: 6px 10px;
        margin: 0;
    }
    .ai-footnote { font-size: 11.5px; color: #64748b; margin: 14px 2px 0; max-width: 80ch; }

    @media (max-width: 991px) {
        .rate-tiles { grid-template-columns: repeat(2, 1fr); }
        .kpi-charts-row-wide { grid-template-columns: 1fr; }
        .ai-top { grid-template-columns: 1fr; }
        .insight-grid { grid-template-columns: 1fr; }
        .exec-line { flex-wrap: wrap; }
    }
    @media (max-width: 575px) {
        .rate-tiles { grid-template-columns: 1fr 1fr; }
        .health-body { flex-direction: column; }
        .view-switch { width: 100%; }
        .view-btn { flex: 1; justify-content: center; padding: 8px 10px; }
        .period-switch { width: 100%; overflow-x: auto; }
        .period-link { flex: 1; text-align: center; white-space: nowrap; }
    }
    @media (prefers-reduced-motion: reduce) {
        .stat-pill, .kpi-metric-item, .view-btn { transition: none; }
    }
</style>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- Export Libraries --}}
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

{{-- ================= JAVASCRIPT ================= --}}
<script>
    // ===== EXPORT HELPERS =====
    function getTableData(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return { headers: [], rows: [] };
        const headerCells = table.querySelectorAll('thead th');
        const headers = Array.from(headerCells).slice(0, -1).map(th => th.innerText.trim());
        const rows = [];
        table.querySelectorAll('tbody tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            const row = Array.from(cells).slice(0, -1).map(td => td.innerText.replace(/\s+/g, ' ').trim());
            if (row.length) rows.push(row);
        });
        return { headers, rows };
    }

    function exportTableCSV(tableId, fileName) {
        const { headers, rows } = getTableData(tableId);
        const escape = v => `"${String(v).replace(/"/g, '""')}"`;
        const csv = [headers.map(escape).join(','), ...rows.map(r => r.map(escape).join(','))].join('\n');
        const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `${fileName}_${new Date().toISOString().slice(0,10)}.csv`;
        a.click();
    }

    function exportTableExcel(tableId, fileName) {
        const { headers, rows } = getTableData(tableId);
        const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Tickets');
        XLSX.writeFile(wb, `${fileName}_${new Date().toISOString().slice(0,10)}.xlsx`);
    }

    function exportTablePDF(tableId, fileName) {
        const { jsPDF } = window.jspdf;
        const { headers, rows } = getTableData(tableId);
        const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
        doc.setFontSize(13);
        doc.setFont('helvetica', 'bold');
        doc.text(fileName.replace(/_/g, ' '), 40, 40);
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.text(`Exported: ${new Date().toLocaleString()}`, 40, 56);
        doc.autoTable({
            head: [headers],
            body: rows,
            startY: 70,
            styles: { fontSize: 8, cellPadding: 4 },
            headStyles: { fillColor: [15, 23, 42], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            margin: { left: 40, right: 40 },
        });
        doc.save(`${fileName}_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    // ---- DATA FROM SERVER ----
    const kpiStats = {
        raised:     {{ $stats['raised'] ?? 0 }},
        pending:    {{ $stats['pending'] ?? 0 }},
        inProcess:  {{ $stats['in_process'] ?? 0 }},
        resolved:   {{ $stats['resolved'] ?? 0 }},
    };
    const KPI = @json($kpiJs);
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ANIM = reduceMotion ? false : { duration: 600 };

    if (window.Chart) {
        Chart.defaults.font.size = 11;
        Chart.defaults.color = '#475569';
    }

    const baseOpts = (extra = {}) => Object.assign({
        responsive: true,
        maintainAspectRatio: false,
        animation: ANIM,
        plugins: { legend: { display: false } },
    }, extra);

    function initKpiCharts() {
        if (!window.Chart) return;

        // Status donut
        const donutCtx = document.getElementById('statusDonutChart');
        if (donutCtx) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Raised', 'Pending', 'In Process', 'Resolved'],
                    datasets: [{
                        data: [kpiStats.raised, kpiStats.pending, kpiStats.inProcess, kpiStats.resolved],
                        backgroundColor: ['#d97706', '#0284c7', '#7c3aed', '#16a34a'],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    cutout: '68%',
                    responsive: true,
                    maintainAspectRatio: true,
                    animation: ANIM,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } },
                }
            });
        }

        // Lifecycle funnel
        const funnelCtx = document.getElementById('funnelChart');
        if (funnelCtx) {
            const labels = Object.keys(KPI.funnel);
            const values = Object.values(KPI.funnel);
            const top = values[0] || 1;
            new Chart(funnelCtx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: ['rgba(217,119,6,0.85)', 'rgba(2,132,199,0.85)', 'rgba(124,58,237,0.85)', 'rgba(22,163,74,0.85)'],
                        borderRadius: 6,
                        barPercentage: 0.75,
                        maxBarThickness: 34,
                    }]
                },
                options: baseOpts({
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x} tickets (${Math.round(ctx.parsed.x / top * 100)}% of raised)` } }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y: { grid: { display: false }, ticks: { font: { weight: '600' } } }
                    }
                })
            });
        }

        // Open-ticket ageing
        const agingCtx = document.getElementById('agingChart');
        if (agingCtx) {
            new Chart(agingCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(KPI.aging),
                    datasets: [{
                        data: Object.values(KPI.aging),
                        backgroundColor: ['#16a34a', '#65a30d', '#d97706', '#ea580c', '#dc2626'],
                        borderRadius: 6,
                        maxBarThickness: 46,
                    }]
                },
                options: baseOpts({
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} open ticket${ctx.parsed.y !== 1 ? 's' : ''}` } } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                })
            });
        }

        // 14-day trend
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: KPI.trend.labels,
                    datasets: [
                        { label: 'Raised', data: KPI.trend.raised, borderColor: '#d97706', backgroundColor: 'rgba(217,119,6,0.12)', fill: true, tension: 0.3, pointRadius: 3, borderWidth: 2 },
                        { label: 'Resolved', data: KPI.trend.resolved, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,0.10)', fill: true, tension: 0.3, pointRadius: 3, borderWidth: 2 },
                    ]
                },
                options: baseOpts({
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true } } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                })
            });
        }

        // Arrival by hour
        const hourlyCtx = document.getElementById('hourlyChart');
        if (hourlyCtx) {
            const hourLabel = h => `${(h % 12) || 12}${h < 12 ? 'a' : 'p'}`;
            const max = Math.max(...KPI.hourly);
            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: KPI.hourly.map((_, h) => hourLabel(h)),
                    datasets: [{
                        data: KPI.hourly,
                        backgroundColor: KPI.hourly.map(v => v === max && v > 0 ? '#0284c7' : 'rgba(2,132,199,0.25)'),
                        borderRadius: 3,
                    }]
                },
                options: baseOpts({
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { title: items => { const h = items[0].dataIndex; return `${hourLabel(h)}–${hourLabel((h + 1) % 24)}`; }, label: ctx => ` ${ctx.parsed.y} tickets` } }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } }
                    }
                })
            });
        }

        // Category breakdown (stacked)
        const catCtx = document.getElementById('categoryChart');
        if (catCtx) {
            const cats = KPI.categories;
            const short = s => s.length > 22 ? s.slice(0, 21) + '…' : s;
            new Chart(catCtx, {
                type: 'bar',
                data: {
                    labels: cats.map(c => short(c.name)),
                    datasets: [
                        { label: 'Resolved', data: cats.map(c => c.resolved), backgroundColor: '#16a34a', borderRadius: 4, maxBarThickness: 26 },
                        { label: 'Open', data: cats.map(c => c.open), backgroundColor: '#d97706', borderRadius: 4, maxBarThickness: 26 },
                    ]
                },
                options: baseOpts({
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true } },
                        tooltip: {
                            callbacks: {
                                title: items => cats[items[0].dataIndex].name,
                                footer: items => `Avg resolution: ${cats[items[0].dataIndex].avg_res}`
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y: { stacked: true, grid: { display: false } }
                    }
                })
            });
        }
    }

    let aiChartsReady = false;
    function initAiCharts() {
        if (aiChartsReady || !window.Chart) return;
        aiChartsReady = true;
        const gaugeCtx = document.getElementById('healthGauge');
        if (gaugeCtx && KPI.health.score !== null) {
            const colors = { good: '#16a34a', info: '#0284c7', warning: '#d97706', critical: '#dc2626' };
            new Chart(gaugeCtx, {
                type: 'doughnut',
                data: { datasets: [{ data: [KPI.health.score, 100 - KPI.health.score], backgroundColor: [colors[KPI.health.level], '#e2e8f0'], borderWidth: 0 }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    rotation: -90,
                    circumference: 180,
                    cutout: '74%',
                    animation: ANIM,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                }
            });
        }
    }

    // ---- TWO-BUTTON VIEW SWITCH ----
    function showView(view) {
        document.querySelectorAll('.view-btn').forEach(b => {
            const on = b.dataset.view === view;
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        document.getElementById('viewKpi').classList.toggle('d-none', view !== 'kpi');
        document.getElementById('viewAi').classList.toggle('d-none', view !== 'ai');
        try { sessionStorage.setItem('ticketsKpiView', view); } catch (e) {}
        if (view === 'ai') initAiCharts();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.view-btn').forEach(b => b.addEventListener('click', () => showView(b.dataset.view)));

        // Perception severity filter
        document.querySelectorAll('.ai-filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.ai-filter-btn').forEach(x => x.classList.toggle('is-active', x === btn));
                const lvl = btn.dataset.level;
                document.querySelectorAll('.insight-card').forEach(card => {
                    card.classList.toggle('d-none', lvl !== 'all' && card.dataset.level !== lvl);
                });
            });
        });

        initKpiCharts();

        let saved = 'kpi';
        try { saved = sessionStorage.getItem('ticketsKpiView') || 'kpi'; } catch (e) {}
        if (location.hash === '#ai') saved = 'ai';
        showView(saved);
    });

    // ---- DIRECTOR REPORT (PDF) ----
    function exportAiReportPDF() {
        if (!window.jspdf) return;
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
        const W = doc.internal.pageSize.getWidth();
        const clean = s => String(s ?? '').replace(/[–—]/g, '-').replace(/→/g, '->').replace(/·/g, '-');

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(16);
        doc.text('Tickets KPI - Director Report', 40, 48);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.setTextColor(100);
        doc.text(`Period: ${KPI.period}    Generated: ${new Date().toLocaleString()}`, 40, 64);
        doc.setTextColor(0);

        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text(`Health score: ${KPI.health.score ?? '-'} / 100 (${KPI.health.label})`, 40, 90);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.text(doc.splitTextToSize(clean(KPI.headline), W - 80), 40, 106);

        const t = KPI.timing, c = KPI.counts, r = KPI.rates;
        doc.autoTable({
            startY: 136,
            head: [['Metric', 'Value']],
            body: [
                ['Total tickets', c.total],
                ['Open / Resolved', `${c.open} / ${c.resolved}`],
                ['Resolution rate', `${r.resolution}%`],
                ['Opened by staff', `${r.acknowledged}%`],
                [`Resolved within ${KPI.sla.resolve_hours}h`, r.sla_resolve === null ? '-' : `${r.sla_resolve}%`],
                ['Avg time to open (Raised -> Pending)', t.first_response.avg_fmt],
                ['Avg Pending -> In Process', t.pending_to_process.avg_fmt],
                ['Avg In Process -> Resolved', t.process_to_resolve.avg_fmt],
                ['Avg resolution (Raised -> Resolved)', `${t.resolution.avg_fmt} (median ${t.resolution.median_fmt})`],
            ].map(row => row.map(clean)),
            styles: { fontSize: 9, cellPadding: 5 },
            headStyles: { fillColor: [15, 23, 42], textColor: 255 },
            columnStyles: { 1: { halign: 'right', fontStyle: 'bold' } },
            margin: { left: 40, right: 40 },
        });

        const levelName = { critical: 'Critical', warning: 'Warning', info: 'Insight', good: 'Good' };
        const levelColor = { critical: [220, 38, 38], warning: [217, 119, 6], info: [2, 132, 199], good: [22, 163, 74] };
        doc.autoTable({
            startY: doc.lastAutoTable.finalY + 20,
            head: [['Level', 'Finding', 'Recommended action']],
            body: KPI.insights.map(i => [levelName[i.level], `${clean(i.title)}\n${clean(i.text)}`, clean(i.action)]),
            styles: { fontSize: 8.5, cellPadding: 5, valign: 'top' },
            headStyles: { fillColor: [15, 23, 42], textColor: 255 },
            columnStyles: { 0: { cellWidth: 58, fontStyle: 'bold' }, 2: { cellWidth: 170 } },
            didParseCell: data => {
                if (data.section === 'body' && data.column.index === 0) {
                    data.cell.styles.textColor = levelColor[KPI.insights[data.row.index].level];
                }
            },
            margin: { left: 40, right: 40 },
        });

        doc.save(`Tickets_KPI_Director_Report_${new Date().toISOString().slice(0, 10)}.pdf`);
    }

    function openViewModal(ticketId) {
        const modalEl = document.getElementById('viewTicketModal');
        const modal = new bootstrap.Modal(modalEl);

        // Reset details
        document.getElementById('modalTktId').textContent = ticketId;
        document.getElementById('modalStudentName').textContent = 'Loading...';
        document.getElementById('modalRegNo').textContent = '—';
        document.getElementById('modalProgram').textContent = '—';
        document.getElementById('modalEmpName').textContent = 'Loading...';
        document.getElementById('modalEmpCode').textContent = '—';
        document.getElementById('modalDuration').textContent = '—';
        document.getElementById('modalRaisedDate').textContent = '—';
        document.getElementById('modalResolvedDate').textContent = '—';
        document.getElementById('modalTimeDifference').textContent = '—';
        document.getElementById('modalQueryText').textContent = 'Fetching ticket details...';
        document.getElementById('modalCategoryBadge').textContent = '—';
        document.getElementById('modalStatusBadge').textContent = 'Loading';
        document.getElementById('modalStatusBadge').className = 'status-chip';
        document.getElementById('modalResolutionSection').classList.add('d-none');
        document.getElementById('modalAttachmentWrap').classList.add('d-none');

        modal.show();

        fetch(`{{ url('visa/ticket-details') }}/${encodeURIComponent(ticketId)}?readonly=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.ticket) {
                const t = data.ticket;
                document.getElementById('modalStudentName').textContent = t.student_name || 'Student';
                document.getElementById('modalRegNo').textContent = t.regno || 'N/A';
                document.getElementById('modalProgram').textContent = t.program || 'N/A';
                document.getElementById('modalEmpName').textContent = t.handler_emp_name || t.assigned_emp_name || 'Unassigned';
                document.getElementById('modalEmpCode').textContent = t.handler_emp_code || t.assigned_emp_code || '—';
                document.getElementById('modalDuration').textContent = t.duration_formatted ? (t.is_resolved ? `${t.duration_formatted} (Resolved)` : `${t.duration_formatted} (Open)`) : '—';
                document.getElementById('modalRaisedDate').textContent = t.raised_date || '—';
                document.getElementById('modalResolvedDate').textContent = t.is_resolved && t.emp_timestamp ? t.emp_timestamp : 'In Progress';
                document.getElementById('modalTimeDifference').textContent = t.duration_formatted || '—';
                document.getElementById('modalQueryText').textContent = t.query || 'No description provided.';
                document.getElementById('modalCategoryBadge').textContent = t.category + (t.sub_type ? ` • ${t.sub_type}` : '');
                
                const badge = document.getElementById('modalStatusBadge');
                badge.textContent = t.status_label;
                badge.className = `status-chip ${t.status_class}`;

                // Resolution Comment
                if (t.emp_tkt_comment) {
                    document.getElementById('modalResolutionComment').textContent = t.emp_tkt_comment;
                    document.getElementById('modalResolutionSection').classList.remove('d-none');
                }

                // File Attachment
                if (t.has_file && t.file_url) {
                    document.getElementById('modalAttachmentLink').href = t.file_url;
                    document.getElementById('modalAttachmentName').textContent = t.emp_tkt_file_name || 'Download Attachment';
                    document.getElementById('modalAttachmentWrap').classList.remove('d-none');
                    document.getElementById('modalResolutionSection').classList.remove('d-none');
                }
            } else {
                document.getElementById('modalQueryText').textContent = 'Failed to load ticket details.';
            }
        })
        .catch(err => {
            console.error('Error fetching ticket details:', err);
            document.getElementById('modalQueryText').textContent = 'An error occurred while loading ticket details.';
        });
    }
</script>
@endsection