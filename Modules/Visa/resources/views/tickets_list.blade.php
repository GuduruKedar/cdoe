@extends('layouts.app')

@section('page_title', 'DSM Tickets Management')

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

    {{-- ================= 1. FOUR STAT BOXES (SAME AS STUDENT) ================= --}}
    <div class="stat-pills-row mb-3">
        {{-- Raised (Code 0) --}}
        <a href="{{ route('visa.ticketslist', ['status' => 0]) }}" class="stat-pill-link text-decoration-none">
            <div class="stat-pill stat-pill-raised {{ (string)$activeStatus === '0' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-raised"></span>Raised
                </div>
                <div class="stat-pill-val" id="statValRaised">{{ $stats['raised'] ?? 0 }}</div>
            </div>
        </a>

        {{-- Pending (Code 1) --}}
        <a href="{{ route('visa.ticketslist', ['status' => 1]) }}" class="stat-pill-link text-decoration-none">
            <div class="stat-pill stat-pill-pending {{ (string)$activeStatus === '1' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-pending"></span>Pending
                </div>
                <div class="stat-pill-val" id="statValPending">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </a>

        {{-- In Process (Code 2) --}}
        <a href="{{ route('visa.ticketslist', ['status' => 2]) }}" class="stat-pill-link text-decoration-none">
            <div class="stat-pill stat-pill-in-process {{ (string)$activeStatus === '2' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-in-process"></span>In Process
                </div>
                <div class="stat-pill-val" id="statValInProcess">{{ $stats['in_process'] ?? 0 }}</div>
            </div>
        </a>

        {{-- Resolved (Code 3) --}}
        <a href="{{ route('visa.ticketslist', ['status' => 3]) }}" class="stat-pill-link text-decoration-none">
            <div class="stat-pill stat-pill-resolved {{ (string)$activeStatus === '3' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-resolved"></span>Resolved
                </div>
                <div class="stat-pill-val" id="statValResolved">{{ $stats['resolved'] ?? 0 }}</div>
            </div>
        </a>
    </div>

    {{-- Horizontal separator line matching user drawing --}}
    <div class="dsm-divider mb-3"></div>

    {{-- ================= 2. TICKETS TABLE CONTAINER ================= --}}
    <div class="sample-rect-box">
        <div class="sample-rect-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="sample-rect-title">Student Support Tickets</span>
                @if ($activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all')
                    <span class="badge bg-secondary text-uppercase fw-normal small">Filter: {{ \Modules\Visa\Models\TicketDetail::STATUS_LABELS[(int)$activeStatus] ?? 'All' }}</span>
                    <a href="{{ route('visa.ticketslist') }}" class="btn btn-sm btn-link text-decoration-none p-0 text-muted small"><i class="bi bi-x-circle"></i> Clear</a>
                @endif
            </div>

            {{-- Search, Records & Download --}}
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('visa.ticketslist') }}" method="GET" class="d-flex align-items-center">
                    @if ($activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all')
                        <input type="hidden" name="status" value="{{ $activeStatus }}">
                    @endif
                    <div class="input-group input-group-sm dsm-search-input">
                        <input type="text" name="search" class="form-control" placeholder="Search ticket / regno..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <span class="sample-rect-badge">{{ $tickets->total() }} records</span>

                {{-- Download Button --}}
                <div class="dropdown" id="downloadDropdownList">
                    <button class="btn-download-tbl" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Download table data">
                        <i class="bi bi-download me-1"></i> Download
                        <i class="bi bi-chevron-down ms-1" style="font-size:10px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 download-menu" style="min-width:150px;">
                        <li>
                            <button class="dropdown-item download-opt" onclick="exportTablePDF('dsmTicketsTable','Student_Support_Tickets')" type="button">
                                <span class="download-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-file-earmark-pdf-fill"></i></span>
                                PDF
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item download-opt" onclick="exportTableExcel('dsmTicketsTable','Student_Support_Tickets')" type="button">
                                <span class="download-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-file-earmark-spreadsheet-fill"></i></span>
                                Excel (.xlsx)
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item download-opt" onclick="exportTableCSV('dsmTicketsTable','Student_Support_Tickets')" type="button">
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
                    <table class="table sample-table align-middle mb-0" id="dsmTicketsTable">
                        <thead>
                            <tr>
                                <th style="width: 125px;">TICKET ID</th>
                                <th style="width: 140px;">STUDENT</th>
                                <th style="width: 155px;">CATEGORY & SUB-TYPE</th>
                                <th>PROBLEM DETAILS</th>
                                <th style="width: 100px;">DATE</th>
                                <th style="width: 95px;">STATUS</th>
                                <th style="width: 80px;" class="text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tickets as $tkt)
                                <tr id="ticketRow-{{ $tkt->tkt_id }}">
                                    <td>
                                        <span class="tkt-code">{{ $tkt->tkt_id }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small">{{ $tkt->regno }}</div>
                                        <div class="text-muted smaller text-truncate" style="max-width: 160px;" title="{{ $tkt->student?->student_name ?? $tkt->student?->name }}">
                                            {{ $tkt->student?->student_name ?? $tkt->student?->name ?? 'Student' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="cat-pill mb-1">{{ $tkt->ticketMaster->tkt_main_type ?? 'N/A' }}</span>
                                        <div class="sub-type-text" title="{{ $tkt->ticketMaster->tkt_sub_type ?? '' }}">
                                            {{ $tkt->ticketMaster->tkt_sub_type ?? $tkt->tkt_head_id }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="query-preview" title="{{ $tkt->std_tkt_query }}">
                                            {{ $tkt->std_tkt_query ?: '—' }}
                                        </div>
                                        @if ($tkt->emp_tkt_comment)
                                            <div class="resolution-preview mt-1 smaller text-success text-truncate" title="Resolution: {{ $tkt->emp_tkt_comment }}">
                                                <i class="bi bi-check2-circle me-1"></i>{{ $tkt->emp_tkt_comment }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-nowrap text-muted small">
                                        {{ $tkt->tkt_raised_datetime ? \Carbon\Carbon::parse($tkt->tkt_raised_datetime)->format('d M Y') : '—' }}
                                    </td>
                                    <td>
                                        <span class="status-chip {{ $tkt->status_class }}" id="statusBadge-{{ $tkt->tkt_id }}">
                                            {{ $tkt->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{-- Info "i" Action Button --}}
                                        <button
                                            type="button"
                                            class="btn-action-i"
                                            onclick="openReviewModal('{{ $tkt->tkt_id }}')"
                                            title="View details & update status">
                                            <i class="bi bi-info-lg"></i>
                                        </button>
                                    </td>
                                </tr>
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
                    <p class="mb-0">No tickets found for this filter.</p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ================= REVIEW & UPDATE MODAL ================= --}}
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="modal-title fs-6 fw-bold text-dark mb-0" id="reviewModalLabel">Ticket Review: <span id="modalTktId" class="text-primary font-monospace"></span></h5>
                    <span id="modalAutoUpdateNotice" class="badge bg-warning-subtle text-warning border border-warning-subtle d-none">
                        <i class="bi bi-eye me-1"></i>Auto-updated to Pending
                    </span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                {{-- Ticket Details Summary --}}
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body p-3">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-4">
                                <span class="text-muted smaller d-block">STUDENT</span>
                                <span class="fw-semibold text-dark small" id="modalStudentName">—</span>
                                <div class="text-muted smaller font-monospace" id="modalRegNo">—</div>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted smaller d-block">PROGRAM</span>
                                <span class="fw-medium text-dark small text-truncate d-block" id="modalProgram">—</span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted smaller d-block">RAISED AT</span>
                                <span class="text-muted small" id="modalRaisedDate">—</span>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted smaller d-block">CATEGORY</span>
                                <span class="badge bg-light text-dark border small fw-semibold" id="modalCategory">—</span>
                                <span class="small text-muted ms-1" id="modalSubType"></span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted smaller d-block">CURRENT STATUS</span>
                                <span class="status-chip" id="modalStatusBadge">—</span>
                            </div>
                        </div>

                        <div class="mt-2 pt-2 border-top">
                            <span class="text-muted smaller d-block fw-semibold">STUDENT QUERY / PROBLEM:</span>
                            <div class="p-2 mt-1 rounded bg-white border small text-dark" id="modalQueryText" style="white-space: pre-wrap;">—</div>
                        </div>
                    </div>
                </div>

                {{-- Status Update Form --}}
                <form id="dsmStatusForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="formTicketId" name="ticket_id">

                    <div class="mb-3">
                        <label class="field-label mb-2 fw-bold text-dark">UPDATE STATUS</label>
                        <div class="status-radio-group">
                            {{-- Option 1: Pending (No extra inputs asked) --}}
                            <label class="status-radio-card" id="cardStatus1">
                                <input type="radio" name="status" value="1" id="radioStatus1">
                                <span class="status-indicator-dot dot-pending"></span>
                                <div class="status-radio-text">
                                    <div class="status-radio-title">Pending</div>
                                    <div class="status-radio-desc">Awaiting review / queued</div>
                                </div>
                            </label>

                            {{-- Option 2: In Process (No extra inputs asked) --}}
                            <label class="status-radio-card" id="cardStatus2">
                                <input type="radio" name="status" value="2" id="radioStatus2">
                                <span class="status-indicator-dot dot-in-process"></span>
                                <div class="status-radio-text">
                                    <div class="status-radio-title">In Process</div>
                                    <div class="status-radio-desc">Work actively in progress</div>
                                </div>
                            </label>

                            {{-- Option 3: Resolved (Requires text, optional file) --}}
                            <label class="status-radio-card" id="cardStatus3">
                                <input type="radio" name="status" value="3" id="radioStatus3">
                                <span class="status-indicator-dot dot-resolved"></span>
                                <div class="status-radio-text">
                                    <div class="status-radio-title">Resolved</div>
                                    <div class="status-radio-desc">Case solved & closed</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Conditional Inputs for Status 3 (Resolved) --}}
                    <div id="resolvedInputsWrap" class="border rounded p-3 mb-3 bg-light-subtle d-none">
                        <div class="d-flex align-items-center gap-1 text-success fw-semibold small mb-2">
                            <i class="bi bi-check-circle-fill"></i> Resolution Details
                        </div>

                        {{-- Text input (REQUIRED for status 3) --}}
                        <div class="mb-3">
                            <label for="emp_tkt_comment" class="form-label small fw-semibold text-dark">
                                Resolution Remarks / Comment <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control form-control-sm"
                                id="emp_tkt_comment"
                                name="emp_tkt_comment"
                                rows="3"
                                placeholder="Explain how the ticket was addressed or resolved (Required for resolution)..."></textarea>
                            <div class="invalid-feedback" id="commentError"></div>
                        </div>

                        {{-- File input (OPTIONAL for status 3) --}}
                        <div class="mb-2">
                            <label for="emp_tkt_file" class="form-label small fw-semibold text-dark">
                                Supporting Document / File <span class="text-muted fw-normal">(Optional field only - PDF, Image, Word, max 10MB)</span>
                            </label>
                            <input
                                type="file"
                                class="form-control form-control-sm"
                                id="emp_tkt_file"
                                name="emp_tkt_file"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.zip">
                            <div class="invalid-feedback" id="fileError"></div>
                            <div id="existingFileHint" class="mt-1 smaller text-muted d-none">
                                Currently attached: <a href="#" id="existingFileLink" target="_blank" class="fw-semibold text-primary"></a>
                            </div>
                        </div>
                    </div>

                    <div id="formErrorMessage" class="alert alert-danger py-2 px-3 small d-none" role="alert"></div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold" id="saveStatusBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="saveSpinner"></span>
                            Save Status Update
                        </button>
                    </div>
                </form>
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
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.08);
    }
    .stat-pill.active-filter {
        border-width: 2.5px;
        border-color: #0284c7;
        background: #f0f9ff;
    }
    .stat-pill-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary, #1e293b);
    }
    .stat-pill-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-primary, #0f172a);
        min-width: 28px;
        text-align: right;
    }

    /* Horizontal separator line matching drawing */
    .dsm-divider {
        height: 2px;
        background: #000000;
        border-radius: 2px;
        margin: 14px 0 16px;
    }

    /* 2. RECTANGLE BOX CONTAINER */
    .sample-rect-box {
        background: var(--bg-card, #ffffff);
        border: 1.5px solid #000000;
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }
    .sample-rect-header {
        background: #fafafa;
        border-bottom: 1.5px solid #000000;
        padding: 8px 14px;
    }
    .sample-rect-title {
        font-size: 13.5px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    .sample-rect-badge {
        font-size: 11.5px;
        font-weight: 700;
        background: #e2e8f0;
        color: #334155;
        padding: 3px 9px;
        border-radius: 10px;
    }

    /* TABLE: Clean layout with NO horizontal scrollbar */
    .table-responsive-dsm {
        width: 100%;
        overflow-x: hidden;
    }
    .sample-table {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0;
    }
    .sample-table th {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .sample-table td {
        padding: 8px 8px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
    }
    .tkt-code {
        font-family: monospace;
        font-weight: 700;
        color: #0284c7;
        font-size: 12px;
    }
    .cat-pill {
        background: #f1f5f9;
        color: #334155;
        font-size: 10.5px;
        padding: 2px 6px;
        border-radius: 5px;
        font-weight: 600;
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sub-type-text {
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
        font-size: 11.5px;
        color: #1e293b;
    }
    .query-preview {
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #64748b;
        font-size: 12px;
    }
    .smaller {
        font-size: 11px;
    }

    /* STATUS CHIPS */
    .status-chip {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 6px;
        display: inline-block;
        white-space: nowrap;
        letter-spacing: 0.02em;
    }
    .status-raised {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .status-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .status-in-process {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .status-resolved {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    /* Status indicator dot */
    .status-indicator-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
        vertical-align: middle;
    }
    .dot-raised { background: #64748b; }
    .dot-pending { background: #f59e0b; }
    .dot-in-process { background: #0284c7; }
    .dot-resolved { background: #16a34a; }

    /* OVAL BUTTON MATCHING USER HAND SKETCH */
    .btn-oval-action {
        background: #ffffff;
        border: 1.5px solid #000000;
        border-radius: 9999px;
        padding: 4px 14px;
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    .btn-oval-action:hover {
        background: #0f172a;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    }

    /* INFO "i" BUTTON */
    .btn-action-i {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1.5px solid #000000;
        background: #ffffff;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        padding: 0;
        line-height: 1;
    }
    .btn-action-i:hover {
        background: #0f172a;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    }

    /* STATUS RADIO GROUP FOR MODAL */
    .status-radio-group {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .status-radio-card {
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.15s ease;
        position: relative;
    }
    .status-radio-card input {
        position: absolute;
        opacity: 0;
    }
    .status-radio-card:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .status-radio-card.selected {
        border-color: #0284c7;
        background: #f0f9ff;
        box-shadow: 0 0 0 1px #0284c7;
    }
    .status-radio-title {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
    }
    .status-radio-desc {
        font-size: 10.5px;
        color: #64748b;
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

{{-- Export Libraries --}}
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

{{-- ================= JAVASCRIPT LOGIC ================= --}}
<script>
    // ===== SHARED EXPORT HELPERS =====
    function getTableData(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return { headers: [], rows: [] };
        const headerCells = table.querySelectorAll('thead th');
        // Exclude last column (ACTION)
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
        // Style header row bold
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let C = range.s.c; C <= range.e.c; C++) {
            const cell = ws[XLSX.utils.encode_cell({ r: 0, c: C })];
            if (cell) cell.s = { font: { bold: true } };
        }
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

    let currentTicket = null;

    function openReviewModal(ticketId) {
        // Reset form & errors
        const form = document.getElementById('dsmStatusForm');
        form.reset();
        document.getElementById('formTicketId').value = ticketId;
        document.getElementById('modalTktId').textContent = ticketId;
        document.getElementById('formErrorMessage').classList.add('d-none');
        document.getElementById('commentError').textContent = '';
        document.getElementById('fileError').textContent = '';
        document.getElementById('emp_tkt_comment').classList.remove('is-invalid');
        document.getElementById('emp_tkt_file').classList.remove('is-invalid');
        document.getElementById('modalAutoUpdateNotice').classList.add('d-none');

        // Show modal
        const modalEl = document.getElementById('reviewModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // Fetch ticket details via AJAX
        // (Server automatically updates status 0 -> 1 when viewed by DSM!)
        fetch(`{{ url('visa/ticket-details') }}/${encodeURIComponent(ticketId)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.ticket) {
                currentTicket = data.ticket;

                // Populate summary
                document.getElementById('modalStudentName').textContent = currentTicket.student_name || 'Student';
                document.getElementById('modalRegNo').textContent = currentTicket.regno || 'N/A';
                document.getElementById('modalProgram').textContent = currentTicket.program || 'N/A';
                document.getElementById('modalRaisedDate').textContent = currentTicket.raised_date || '—';
                document.getElementById('modalCategory').textContent = currentTicket.category || 'N/A';
                document.getElementById('modalSubType').textContent = currentTicket.sub_type ? `• ${currentTicket.sub_type}` : '';
                document.getElementById('modalQueryText').textContent = currentTicket.query || 'No description provided.';

                const statusBadge = document.getElementById('modalStatusBadge');
                statusBadge.className = `status-chip ${currentTicket.status_class}`;
                statusBadge.textContent = currentTicket.status_label;

                // If status was automatically updated from 0 to 1 upon viewing:
                if (data.status_changed) {
                    document.getElementById('modalAutoUpdateNotice').classList.remove('d-none');

                    // Update table row badge in real-time
                    const rowBadge = document.getElementById(`statusBadge-${currentTicket.tkt_id}`);
                    if (rowBadge) {
                        rowBadge.className = `status-chip ${currentTicket.status_class}`;
                        rowBadge.textContent = currentTicket.status_label;
                    }

                    // Update top counters if returned
                    if (data.stats) {
                        updateCounterPills(data.stats);
                    }
                }

                // Pre-select current status in the radio buttons
                const currentStatusCode = Math.max(1, currentTicket.std_tkt_status); // 1, 2, or 3
                selectStatusRadio(currentStatusCode);

                // Populate existing comment/file if already resolved
                if (currentTicket.std_tkt_status === 3) {
                    document.getElementById('emp_tkt_comment').value = currentTicket.emp_tkt_comment || '';
                    if (currentTicket.has_file && currentTicket.file_url) {
                        const hint = document.getElementById('existingFileHint');
                        const link = document.getElementById('existingFileLink');
                        hint.classList.remove('d-none');
                        link.href = currentTicket.file_url;
                        link.textContent = currentTicket.emp_tkt_file_name || 'Download file';
                    }
                } else {
                    document.getElementById('existingFileHint').classList.add('d-none');
                }
            }
        })
        .catch(err => {
            console.error('Failed to load ticket details:', err);
        });
    }

    function selectStatusRadio(val) {
        val = parseInt(val);
        [1, 2, 3].forEach(s => {
            const card = document.getElementById(`cardStatus${s}`);
            const radio = document.getElementById(`radioStatus${s}`);
            if (s === val) {
                card.classList.add('selected');
                radio.checked = true;
            } else {
                card.classList.remove('selected');
                radio.checked = false;
            }
        });

        // "for 1,2 dont ask any input, but for 3 ask inputs a text(required) and a file input(optional field only)"
        const wrap = document.getElementById('resolvedInputsWrap');
        const commentInput = document.getElementById('emp_tkt_comment');

        if (val === 3) {
            wrap.classList.remove('d-none');
            commentInput.required = true;
        } else {
            wrap.classList.add('d-none');
            commentInput.required = false;
            commentInput.value = '';
        }
    }

    // Attach click listeners to status radio cards
    [1, 2, 3].forEach(s => {
        const card = document.getElementById(`cardStatus${s}`);
        if (card) {
            card.addEventListener('click', () => selectStatusRadio(s));
        }
    });

    // Handle Form Submit via AJAX
    document.getElementById('dsmStatusForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = this;
        const ticketId = document.getElementById('formTicketId').value;
        const selectedRadio = document.querySelector('input[name="status"]:checked');
        const errAlert = document.getElementById('formErrorMessage');
        errAlert.classList.add('d-none');
        document.getElementById('commentError').textContent = '';
        document.getElementById('emp_tkt_comment').classList.remove('is-invalid');

        if (!selectedRadio) {
            errAlert.textContent = 'Please choose a status.';
            errAlert.classList.remove('d-none');
            return;
        }

        const statusCode = parseInt(selectedRadio.value);
        const comment = document.getElementById('emp_tkt_comment').value.trim();

        // Validation for status 3
        if (statusCode === 3 && comment.length === 0) {
            document.getElementById('emp_tkt_comment').classList.add('is-invalid');
            document.getElementById('commentError').textContent = 'Resolution remarks are required when resolving a ticket.';
            return;
        }

        const saveBtn = document.getElementById('saveStatusBtn');
        const spinner = document.getElementById('saveSpinner');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        const formData = new FormData(form);

        fetch(`{{ url('visa/ticket-status') }}/${encodeURIComponent(ticketId)}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("Server raw response:", text);
                const tmp = document.createElement("DIV");
                tmp.innerHTML = text;
                const cleanMsg = tmp.textContent || tmp.innerText || "Failed to process upload. Please try again.";
                throw { message: cleanMsg.trim() };
            }
            if (!res.ok) {
                throw data;
            }
            return data;
        })
        .then(data => {
            saveBtn.disabled = false;
            spinner.classList.add('d-none');

            if (data.success) {
                // Update table row status badge
                const rowBadge = document.getElementById(`statusBadge-${ticketId}`);
                if (rowBadge) {
                    rowBadge.className = `status-chip ${data.status_class}`;
                    rowBadge.textContent = data.status_label;
                }

                // Update counter pills
                if (data.stats) {
                    updateCounterPills(data.stats);
                }

                // Close modal
                const modalEl = document.getElementById('reviewModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }

                // Show toast / notification
                alertSuccess(data.message || 'Ticket status updated successfully!');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
            if (err.errors && err.errors.emp_tkt_comment) {
                document.getElementById('emp_tkt_comment').classList.add('is-invalid');
                document.getElementById('commentError').textContent = err.errors.emp_tkt_comment[0];
            } else {
                errAlert.textContent = err.message || 'An error occurred while updating status.';
                errAlert.classList.remove('d-none');
            }
        });
    });

    function updateCounterPills(stats) {
        if (!stats) return;
        if (document.getElementById('statValRaised')) document.getElementById('statValRaised').textContent = stats.raised ?? 0;
        if (document.getElementById('statValPending')) document.getElementById('statValPending').textContent = stats.pending ?? 0;
        if (document.getElementById('statValInProcess')) document.getElementById('statValInProcess').textContent = stats.in_process ?? 0;
        if (document.getElementById('statValResolved')) document.getElementById('statValResolved').textContent = stats.resolved ?? 0;
    }

    function alertSuccess(msg) {
        const div = document.createElement('div');
        div.className = 'alert alert-success alert-dismissible fade show shadow-sm position-fixed top-0 end-0 m-3 py-2 px-3';
        div.style.zIndex = '9999';
        div.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>${msg}<button type="button" class="btn-close btn-close-sm ms-2" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 4000);
    }
</script>
@endsection
