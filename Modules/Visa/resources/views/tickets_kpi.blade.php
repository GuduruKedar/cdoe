@extends('layouts.app')

@section('page_title', 'Tickets KPI & Performance')

@section('content')
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

    {{-- ================= 1. FOUR FILTER STAT CARDS + TIME SUMMARY ================= --}}
    <div class="stat-pills-row mb-3">
        {{-- Raised (Code 0) --}}
        <a href="{{ (string)$activeStatus === '0' ? route('visa.ticketskpi') : route('visa.ticketskpi', ['status' => 0]) }}" class="stat-pill-link text-decoration-none">
            <div class="stat-pill stat-pill-raised {{ (string)$activeStatus === '0' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-raised"></span>Raised
                </div>
                <div class="stat-pill-val" id="statValRaised">{{ $stats['raised'] ?? 0 }}</div>
            </div>
        </a>

        {{-- Pending (Code 1) --}}
        <a href="{{ (string)$activeStatus === '1' ? route('visa.ticketskpi') : route('visa.ticketskpi', ['status' => 1]) }}" class="stat-pill-link text-decoration-none">
            <div class="stat-pill stat-pill-pending {{ (string)$activeStatus === '1' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-pending"></span>Pending
                </div>
                <div class="stat-pill-val" id="statValPending">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </a>

        {{-- In Process (Code 2) --}}
        <a href="{{ (string)$activeStatus === '2' ? route('visa.ticketskpi') : route('visa.ticketskpi', ['status' => 2]) }}" class="stat-pill-link text-decoration-none">
            <div class="stat-pill stat-pill-in-process {{ (string)$activeStatus === '2' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-in-process"></span>In Process
                </div>
                <div class="stat-pill-val" id="statValInProcess">{{ $stats['in_process'] ?? 0 }}</div>
            </div>
        </a>

        {{-- Resolved (Code 3) --}}
        <a href="{{ (string)$activeStatus === '3' ? route('visa.ticketskpi') : route('visa.ticketskpi', ['status' => 3]) }}" class="stat-pill-link text-decoration-none">
            <div class="stat-pill stat-pill-resolved {{ (string)$activeStatus === '3' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-resolved"></span>Resolved
                </div>
                <div class="stat-pill-val" id="statValResolved">{{ $stats['resolved'] ?? 0 }}</div>
            </div>
        </a>

        {{-- 5. Time Summary Box --}}
        <div class="stat-pill stat-pill-time-summary" title="Average Ticket Handling & Resolution Times">
            <div class="time-summary-body">
                <div class="time-summary-item">
                    <span class="time-summary-label">
                        <i class="bi bi-hourglass-split time-icon" style="color:#0284c7;"></i>
                        <span>Average Pending time</span>
                        <span class="time-summary-sep">::</span>
                    </span>
                    <span class="time-summary-val val-pending" id="statAvgPending">{{ $avgPendingFormatted }}</span>
                </div>
                <div class="time-summary-item">
                    <span class="time-summary-label">
                        <i class="bi bi-check2-circle time-icon" style="color:#16a34a;"></i>
                        <span>Average Resolve time</span>
                        <span class="time-summary-sep">::</span>
                    </span>
                    <span class="time-summary-val val-resolve" id="statAvgResolve">{{ $avgResolutionFormatted }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Horizontal separator line --}}
    <div class="dsm-divider mb-3"></div>

    {{-- ================= 2. CHARTS ROW ================= --}}
    <div class="kpi-charts-row mb-3">

        {{-- Donut: Status Distribution --}}
        <div class="kpi-chart-card">
            <div class="kpi-chart-card-header">
                <i class="bi bi-pie-chart-fill text-primary me-2"></i>
                <span>Status Distribution</span>
            </div>
            <div class="kpi-chart-body donut-wrap">
                <canvas id="statusDonutChart" height="220"></canvas>
                <div class="donut-legend">
                    <div class="donut-legend-item"><span class="donut-dot" style="background:#d97706"></span>Raised <strong>{{ $stats['raised'] }}</strong></div>
                    <div class="donut-legend-item"><span class="donut-dot" style="background:#0284c7"></span>Pending <strong>{{ $stats['pending'] }}</strong></div>
                    <div class="donut-legend-item"><span class="donut-dot" style="background:#7c3aed"></span>In Process <strong>{{ $stats['in_process'] }}</strong></div>
                    <div class="donut-legend-item"><span class="donut-dot" style="background:#16a34a"></span>Resolved <strong>{{ $stats['resolved'] }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Bar: Ticket Volume by Status --}}
        <div class="kpi-chart-card">
            <div class="kpi-chart-card-header">
                <i class="bi bi-bar-chart-fill text-indigo me-2"></i>
                <span>Ticket Volume by Status</span>
            </div>
            <div class="kpi-chart-body">
                <canvas id="statusBarChart" height="220"></canvas>
            </div>
        </div>

        {{-- KPI Summary Card --}}
        <div class="kpi-chart-card">
            <div class="kpi-chart-card-header">
                <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                <span>Key Metrics</span>
            </div>
            <div class="kpi-chart-body kpi-metrics-grid">
                @php
                    $total = $stats['total'] ?? 0;
                    $resolved = $stats['resolved'] ?? 0;
                    $resRate = $total > 0 ? round(($resolved / $total) * 100) : 0;
                    $openTickets = ($stats['raised'] ?? 0) + ($stats['pending'] ?? 0) + ($stats['in_process'] ?? 0);
                @endphp
                <div class="kpi-metric-item">
                    <div class="kpi-metric-icon" style="background:#eff6ff; color:#1d4ed8"><i class="bi bi-ticket-detailed-fill"></i></div>
                    <div>
                        <div class="kpi-metric-val">{{ $total }}</div>
                        <div class="kpi-metric-label">Total Tickets</div>
                    </div>
                </div>
                <div class="kpi-metric-item">
                    <div class="kpi-metric-icon" style="background:#f0fdf4; color:#16a34a"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <div class="kpi-metric-val" style="color:#16a34a">{{ $resRate }}%</div>
                        <div class="kpi-metric-label">Resolution Rate</div>
                    </div>
                </div>
                <div class="kpi-metric-item">
                    <div class="kpi-metric-icon" style="background:#fef9c3; color:#ca8a04"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="kpi-metric-val" style="color:#d97706">{{ $openTickets }}</div>
                        <div class="kpi-metric-label">Open Tickets</div>
                    </div>
                </div>
                <div class="kpi-metric-item">
                    <div class="kpi-metric-icon" style="background:#faf5ff; color:#7c3aed"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <div class="kpi-metric-val" style="color:#7c3aed">{{ $slaRate ?? 100 }}%</div>
                        <div class="kpi-metric-label">SLA Met (&lt;24h)</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= 3. TICKETS PERFORMANCE TABLE CONTAINER ================= --}}
    <div class="sample-rect-box">
        <div class="sample-rect-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="sample-rect-title">Resolution & Response Metrics</span>
                @if ($activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all')
                    <span class="badge bg-secondary text-uppercase fw-normal small">
                        Filter: {{ \Modules\Visa\Models\TicketDetail::STATUS_LABELS[(int)$activeStatus] ?? 'All' }}
                    </span>
                    <a href="{{ route('visa.ticketskpi') }}" class="btn btn-sm btn-link text-decoration-none p-0 text-muted small">
                        <i class="bi bi-x-circle"></i> Clear Filter
                    </a>
                @endif
            </div>

            {{-- Search, Records & Download --}}
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('visa.ticketskpi') }}" method="GET" class="d-flex align-items-center">
                    @if ($activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all')
                        <input type="hidden" name="status" value="{{ $activeStatus }}">
                    @endif
                    <div class="input-group input-group-sm dsm-search-input">
                        <input type="text" name="search" class="form-control" placeholder="Search ticket, regno..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <span class="sample-rect-badge">{{ $tickets->total() }} records</span>

                {{-- Download Button --}}
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
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle smaller py-0 px-1">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($tkt->is_resolved)
                                            <span class="duration-badge duration-badge-resolved py-0 px-2 fw-semibold" title="Solved in {{ $tkt->duration_formatted }}">
                                                <i class="bi bi-check2-circle me-1 text-success"></i>{{ $tkt->duration_formatted }}
                                            </span>
                                        @else
                                            <span class="duration-badge duration-badge-pending py-0 px-2 fw-semibold" title="Open for {{ $tkt->duration_formatted }}">
                                                <i class="bi bi-clock-history me-1 text-warning"></i>{{ $tkt->duration_formatted }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-chip {{ $tkt->status_class }} py-0 px-2 smaller">
                                            {{ $tkt->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <button
                                            type="button"
                                            class="btn-action-view"
                                            onclick="openViewModal('{{ $tkt->tkt_id }}')"
                                            title="View ticket details">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="text-muted small">Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} tickets</span>
                    <div>{{ $tickets->links() }}</div>
                </div>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    <p class="mb-0">No tickets found for the selected filter.</p>
                </div>
            @endif
        </div>
    </div>

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
                <div id="modalResolutionSection" class="p-3 rounded border bg-success-subtle mb-2 d-none">
                    <div class="fw-bold text-success smaller text-uppercase mb-1 d-flex align-items-center gap-1">
                        <i class="bi bi-check-circle-fill"></i> Staff Resolution Remarks
                    </div>
                    <div class="small text-dark mb-2" id="modalResolutionComment"></div>
                    <div id="modalAttachmentWrap" class="d-none pt-2 border-top border-success-subtle">
                        <span class="smaller text-muted me-2">Attached File:</span>
                        <a href="#" id="modalAttachmentLink" target="_blank" class="btn btn-xs btn-outline-success btn-sm">
                            <i class="bi bi-download me-1"></i> <span id="modalAttachmentName">Download File</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                <a href="{{ route('visa.ticketslist') }}" class="btn btn-sm btn-link text-decoration-none small text-secondary">
                    <i class="bi bi-pencil-square me-1"></i> Open Tickets Management
                </a>
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Close</button>
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

    /* 1. TOP ROW: 4 COMPACT FILTER PILLS + 1 TIME SUMMARY BOX */
    .stat-pills-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr) 1.55fr;
        gap: 12px;
        align-items: stretch;
    }
    .stat-pill {
        background: var(--bg-card, #ffffff);
        border: 1.5px solid #000000;
        border-radius: 12px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
        height: 100%;
        min-height: 48px;
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
        white-space: nowrap;
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

    /* 5th Box: Time Summary */
    .stat-pill-time-summary {
        background: #ffffff;
        border: 1.5px solid #4f46e5 !important;
        border-radius: 12px;
        padding: 6px 14px;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        cursor: default;
        height: 100%;
        min-height: 48px;
    }
    .stat-pill-time-summary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.12);
    }
    .time-summary-body {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 3px;
        width: 100%;
    }
    .time-summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        font-size: 11.5px;
        line-height: 1.25;
    }
    .time-summary-label {
        color: #334155;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        font-size: 11.5px;
    }
    .time-summary-label .time-icon {
        font-size: 11.5px;
    }
    .time-summary-sep {
        color: #64748b;
        font-weight: 700;
        margin-left: 1px;
    }
    .time-summary-val {
        font-size: 11.5px;
        font-weight: 800;
        font-family: inherit;
        white-space: nowrap;
        text-align: right;
    }
    .time-summary-val.val-pending {
        color: #0284c7;
    }
    .time-summary-val.val-resolve {
        color: #16a34a;
    }

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

    @media (max-width: 1199px) {
        .stat-pills-row {
            grid-template-columns: repeat(4, 1fr) 1.55fr;
        }
    }
    @media (max-width: 991px) {
        .stat-pills-row {
            grid-template-columns: repeat(2, 1fr);
        }
        .stat-pill-time-summary {
            grid-column: span 2;
        }
    }
    @media (max-width: 575px) {
        .stat-pills-row {
            grid-template-columns: 1fr;
        }
        .stat-pill-time-summary {
            grid-column: span 1;
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

    // ---- CHART DATA FROM SERVER ----
    const kpiStats = {
        raised:     {{ $stats['raised'] ?? 0 }},
        pending:    {{ $stats['pending'] ?? 0 }},
        inProcess:  {{ $stats['in_process'] ?? 0 }},
        resolved:   {{ $stats['resolved'] ?? 0 }},
    };

    document.addEventListener('DOMContentLoaded', function () {

        // 1. Donut Chart — Status Distribution
        const donutCtx = document.getElementById('statusDonutChart');
        if (donutCtx) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Raised', 'Pending', 'In Process', 'Resolved'],
                    datasets: [{
                        data: [kpiStats.raised, kpiStats.pending, kpiStats.inProcess, kpiStats.resolved],
                        backgroundColor: ['#d97706', '#0284c7', '#7c3aed', '#16a34a'],
                        borderColor: ['#fff','#fff','#fff','#fff'],
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    cutout: '68%',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                            }
                        }
                    },
                    animation: { animateRotate: true, duration: 700 }
                }
            });
        }

        // 2. Bar Chart — Ticket Volume by Status
        const barCtx = document.getElementById('statusBarChart');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Raised', 'Pending', 'In Process', 'Resolved'],
                    datasets: [{
                        label: 'Tickets',
                        data: [kpiStats.raised, kpiStats.pending, kpiStats.inProcess, kpiStats.resolved],
                        backgroundColor: [
                            'rgba(217,119,6,0.18)',
                            'rgba(2,132,199,0.18)',
                            'rgba(124,58,237,0.18)',
                            'rgba(22,163,74,0.18)',
                        ],
                        borderColor: ['#d97706','#0284c7','#7c3aed','#16a34a'],
                        borderWidth: 2,
                        borderRadius: 7,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} ticket${ctx.parsed.y !== 1 ? 's' : ''}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { size: 11 } },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            ticks: { font: { size: 11 } },
                            grid: { display: false }
                        }
                    },
                    animation: { duration: 700 }
                }
            });
        }
    });

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
