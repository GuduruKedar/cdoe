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

    {{-- ================= 1. FOUR FILTER STAT CARDS ================= --}}
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
    </div>

    {{-- Horizontal separator line --}}
    <div class="dsm-divider mb-3"></div>

    {{-- ================= 2. TICKETS PERFORMANCE TABLE CONTAINER ================= --}}
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

            {{-- Search & Total Records --}}
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
</style>

{{-- ================= JAVASCRIPT ================= --}}
<script>
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
