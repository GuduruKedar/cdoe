@extends('layouts.app')

@section('page_title', 'My Tickets')

@section('content')
<div class="tickets-container">

    {{-- Flash Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3 py-2 ps-3 pe-5 shadow-sm position-relative" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5 me-2 flex-shrink-0"></i>
            <div class="small fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3 py-2 ps-3 pe-5 shadow-sm position-relative" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5 me-2 flex-shrink-0"></i>
            <div class="small fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3 py-2 ps-3 pe-5 shadow-sm position-relative" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-x-circle-fill text-danger fs-5 mt-0 flex-shrink-0"></i>
                <div>
                    <div class="small fw-bold">Please check the form:</div>
                    <ul class="mb-0 ps-3 small mt-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ================= 1. FOUR COMPACT STAT BOXES (CLICKABLE FILTERS) ================= --}}
    <div class="stat-pills-row mb-3">
        {{-- Raised (Code 0) --}}
        <a href="{{ (string)($activeStatus ?? '') === '0' ? route('visa.ticketraise') . '#records-section' : route('visa.ticketraise', ['status' => 0]) . '#records-section' }}" class="stat-pill-link text-decoration-none" title="Filter by Raised tickets">
            <div class="stat-pill stat-pill-raised {{ (string)($activeStatus ?? '') === '0' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-raised"></span>Raised
                </div>
                <div class="stat-pill-val">{{ $stats['raised'] ?? 0 }}</div>
            </div>
        </a>

        {{-- Pending (Code 1) --}}
        <a href="{{ (string)($activeStatus ?? '') === '1' ? route('visa.ticketraise') . '#records-section' : route('visa.ticketraise', ['status' => 1]) . '#records-section' }}" class="stat-pill-link text-decoration-none" title="Filter by Pending tickets">
            <div class="stat-pill stat-pill-pending {{ (string)($activeStatus ?? '') === '1' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-pending"></span>Pending
                </div>
                <div class="stat-pill-val">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </a>

        {{-- In Process (Code 2) --}}
        <a href="{{ (string)($activeStatus ?? '') === '2' ? route('visa.ticketraise') . '#records-section' : route('visa.ticketraise', ['status' => 2]) . '#records-section' }}" class="stat-pill-link text-decoration-none" title="Filter by In Process tickets">
            <div class="stat-pill stat-pill-in-process {{ (string)($activeStatus ?? '') === '2' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-in-process"></span>In Process
                </div>
                <div class="stat-pill-val">{{ $stats['in_process'] ?? 0 }}</div>
            </div>
        </a>

        {{-- Resolved (Code 3) --}}
        <a href="{{ (string)($activeStatus ?? '') === '3' ? route('visa.ticketraise') . '#records-section' : route('visa.ticketraise', ['status' => 3]) . '#records-section' }}" class="stat-pill-link text-decoration-none" title="Filter by Resolved tickets">
            <div class="stat-pill stat-pill-resolved {{ (string)($activeStatus ?? '') === '3' ? 'active-filter' : '' }}">
                <div class="stat-pill-label">
                    <span class="status-indicator-dot dot-resolved"></span>Resolved
                </div>
                <div class="stat-pill-val">{{ $stats['resolved'] ?? 0 }}</div>
            </div>
        </a>
    </div>

    {{-- ================= 2. MY TICKETS & RAISE FORM ================= --}}
    <div class="my-tickets-card mb-4">
        <div class="my-tickets-header">
            <h3 class="my-tickets-title">My Tickets</h3>
            <p class="my-tickets-sub">Raise and track support requests</p>
        </div>

        <div class="raise-ticket-inner">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="raise-ticket-title mb-0">Raise New Ticket</h5>
                <span class="self-service-tag">Student self-service</span>
            </div>

            <form action="{{ route('visa.ticketraise.store') }}" method="POST" id="ticketRaiseForm" enctype="multipart/form-data" novalidate>
                @csrf

                {{-- REQUEST TYPE: Two clean dropdowns --}}
                <div class="mb-3">
                    <label class="field-label">REQUEST TYPE</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <select class="form-select clean-select" id="tkt_main_type" name="tkt_main_type" required>
                                <option value="" disabled selected>Select category (e.g. Exam Query, Document...)</option>
                                @foreach ($mainTypes as $mType)
                                    <option value="{{ $mType }}" {{ old('tkt_main_type') == $mType ? 'selected' : '' }}>
                                        {{ $mType }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select clean-select" id="tkt_head_id" name="tkt_head_id" required disabled>
                                <option value="" disabled selected>Select specific sub-type</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- QUERY: Reason / Problem Textarea (Strictly Required) --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="field-label mb-0" for="std_tkt_query">
                            QUERY <span class="text-danger fw-bold">*</span>
                            <span class="text-danger fw-semibold" style="font-size: 11px;">(Required)</span>
                        </label>
                        <span class="char-hint" id="charCounter">0 / 500</span>
                    </div>
                    <textarea
                        class="form-control clean-textarea @error('std_tkt_query') is-invalid @enderror"
                        id="std_tkt_query"
                        name="std_tkt_query"
                        rows="3"
                        maxlength="500"
                        placeholder="Please describe your query or problem in detail (Required)..."
                        required>{{ old('std_tkt_query') }}</textarea>
                    <div class="text-danger smaller mt-1 d-none" id="queryError" style="font-size: 11.5px; font-weight: 600;">
                        <i class="bi bi-exclamation-circle me-1"></i>Please enter the problem details. This field is required.
                    </div>
                    @error('std_tkt_query')
                        <div class="text-danger smaller mt-1" style="font-size: 11.5px; font-weight: 600;">
                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- ATTACHMENT: Optional Supporting Document / Screenshot (Not Mandatory / not *) --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="field-label mb-0" for="std_tkt_file">
                            ATTACHMENT <span class="text-muted fw-normal" style="font-size: 11px;">(Optional — PDF, JPG, PNG, DOC, ZIP up to 10MB)</span>
                        </label>
                        <span class="text-muted smaller" style="font-size: 11px;">Optional</span>
                    </div>
                    <div class="input-group">
                        <input
                            type="file"
                            class="form-control clean-file-input @error('std_tkt_file') is-invalid @enderror"
                            id="std_tkt_file"
                            name="std_tkt_file"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.zip,.txt,.xls,.xlsx,.csv">
                        <button class="btn btn-outline-secondary btn-sm d-none" type="button" id="clearFileBtn" title="Clear attached file">
                            <i class="bi bi-x"></i> Clear
                        </button>
                    </div>
                    <div class="text-muted smaller mt-1" style="font-size: 11px;">
                        <i class="bi bi-info-circle me-1"></i>You can optionally upload a supporting document or screenshot to help us resolve your issue faster.
                    </div>
                    @error('std_tkt_file')
                        <div class="text-danger smaller mt-1" style="font-size: 11.5px; font-weight: 600;">
                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Bottom Action Row --}}
                <div class="d-flex justify-content-between align-items-center pt-1">
                    <div class="student-meta-hint">
                        <span>Reg No: <strong>{{ $student->register_no ?? ($user->usercode ?? 'N/A') }}</strong></span>
                    </div>
                    <button type="submit" class="btn btn-raise-red" id="submitBtn">
                        Raise Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= 3. SAMPLE RECTANGLE BOX ================= --}}
    <div class="sample-rect-box" id="records-section">
        <div class="sample-rect-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="sample-rect-title">Ticket Records & History</span>
                @if (isset($activeStatus) && $activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all')
                    <span class="badge bg-secondary text-uppercase fw-normal small">Filter: {{ \Modules\Visa\Models\TicketDetail::STATUS_LABELS[(int)$activeStatus] ?? 'All' }}</span>
                    <a href="{{ route('visa.ticketraise') }}#records-section" class="btn btn-sm btn-link text-decoration-none p-0 text-muted small"><i class="bi bi-x-circle"></i> Clear</a>
                @endif
            </div>
            <span class="sample-rect-badge">{{ count($recentTickets) }} records</span>
        </div>

        <div class="sample-rect-body">
            @if ($recentTickets->isNotEmpty())
                <div class="table-responsive">
                    <table class="table sample-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 140px;">TICKET ID</th>
                                <th>CATEGORY</th>
                                <th>SUB-TYPE / QUERY</th>
                                <th>REASON</th>
                                <th style="width: 120px;">DATE</th>
                                <th style="width: 105px;">STATUS</th>
                                <th style="width: 155px;" class="text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentTickets as $tkt)
                                <tr>
                                    <td>
                                        <span class="tkt-code">{{ $tkt->tkt_id }}</span>
                                    </td>
                                    <td>
                                        <span class="cat-pill">{{ $tkt->ticketMaster->tkt_main_type ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="sub-type-text" title="{{ $tkt->ticketMaster->tkt_sub_type ?? '' }}">
                                            {{ $tkt->ticketMaster->tkt_sub_type ?? $tkt->tkt_head_id }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="query-preview" title="{{ $tkt->std_tkt_query }}">
                                            {{ $tkt->std_tkt_query ?: '—' }}
                                        </div>
                                        @if ($tkt->std_tkt_file_path)
                                            <div class="mt-1">
                                                <a href="{{ route('visa.ticket.download_student', $tkt->tkt_id) }}" class="badge bg-light text-primary border border-primary-subtle text-decoration-none py-1 px-2" title="Download your attached document">
                                                    <i class="bi bi-paperclip me-1"></i>{{ $tkt->std_tkt_file_name ?: 'My Attachment' }}
                                                </a>
                                            </div>
                                        @endif
                                        @if ($tkt->emp_tkt_comment)
                                            <div class="mt-1 smaller text-success fw-medium text-truncate" style="max-width: 260px;" title="Resolution: {{ $tkt->emp_tkt_comment }}">
                                                <i class="bi bi-check2-circle me-1"></i>{{ $tkt->emp_tkt_comment }}
                                            </div>
                                        @endif
                                        @if ($tkt->emp_tkt_file_path)
                                            <div class="mt-1">
                                                <a href="{{ route('visa.ticket.download', $tkt->tkt_id) }}" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none py-1 px-2" title="Download resolution attachment">
                                                    <i class="bi bi-paperclip me-1"></i>{{ $tkt->emp_tkt_file_name ?: 'Attachment' }}
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-nowrap text-muted small">
                                        {{ $tkt->tkt_raised_datetime ? \Carbon\Carbon::parse($tkt->tkt_raised_datetime)->format('d M Y') : '—' }}
                                    </td>
                                    <td>
                                        @if ($tkt->std_tkt_status === 0 || $tkt->std_tkt_status === '0')
                                            <span class="status-chip status-raised">Raised</span>
                                        @elseif ($tkt->std_tkt_status === 1 || $tkt->std_tkt_status === '1')
                                            <span class="status-chip status-pending">Pending</span>
                                        @elseif ($tkt->std_tkt_status === 2 || $tkt->std_tkt_status === '2')
                                            <span class="status-chip status-in-process">In Process</span>
                                        @elseif ($tkt->std_tkt_status === 3 || $tkt->std_tkt_status === '3')
                                            <span class="status-chip status-resolved">Resolved</span>
                                        @else
                                            <span class="status-chip">{{ $tkt->status_label ?? ('Status ' . $tkt->std_tkt_status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button type="button"
                                                class="btn-oval-action {{ $tkt->std_tkt_status === 3 ? 'btn-oval-success' : '' }}"
                                                onclick="openStudentSolutionModal('{{ $tkt->tkt_id }}')">
                                            {{ $tkt->std_tkt_status === 3 ? 'View Solution' : 'View Details' }}
                                        </button>
                                        <button type="button"
                                                class="btn-oval-action btn-oval-danger ms-1"
                                                onclick="confirmDeleteTicket('{{ $tkt->tkt_id }}')"
                                                title="Delete Ticket">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Clean minimal lines placeholder matching user drawing --}}
                <div class="empty-lines-wrap">
                    <div class="dummy-line" style="width: 75%;"></div>
                    <div class="dummy-line" style="width: 65%;"></div>
                    <div class="dummy-line" style="width: 80%;"></div>
                    <div class="dummy-line" style="width: 70%;"></div>
                    <div class="empty-note text-center mt-3 text-muted small">
                        No tickets created yet. When you raise a ticket above, it will appear here.
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ================= 4. STUDENT SOLUTION & DETAILS MODAL ================= --}}
<div class="modal fade" id="studentDetailModal" tabindex="-1" aria-labelledby="studentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-light py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark font-monospace px-2 py-1" id="stdModalTktId">TKT#</span>
                    <span class="fw-bold text-dark small" id="stdModalCategoryTitle">Ticket Details</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="stdModalStatusChip" class="status-chip">Pending</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3">
                {{-- Spinner --}}
                <div id="stdModalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading details...</span>
                    </div>
                    <div class="small text-muted mt-2">Loading ticket details...</div>
                </div>

                {{-- Content Container --}}
                <div id="stdModalContent" class="d-none">
                    {{-- Basic Ticket Meta Row --}}
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted smaller d-block fw-semibold text-uppercase">Category</span>
                                <span class="fw-bold text-dark small" id="stdModalCategory"></span>
                                <span class="small text-muted" id="stdModalSubType"></span>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <span class="text-muted smaller d-block fw-semibold text-uppercase">Raised On</span>
                                <span class="small text-dark font-monospace" id="stdModalRaisedDate"></span>
                            </div>
                        </div>

                        <div class="mt-2 pt-2 border-top">
                            <span class="text-muted smaller d-block fw-semibold text-uppercase mb-1">Your Problem Description / Query:</span>
                            <div class="p-2 rounded bg-white border small text-dark" id="stdModalQuery" style="white-space: pre-wrap; font-size: 12.5px;"></div>
                            
                            {{-- Attached File by Student --}}
                            <div id="stdModalStudentAttachmentWrap" class="mt-2 pt-2 border-top d-none">
                                <span class="text-muted smaller fw-semibold me-2"><i class="bi bi-paperclip"></i> Your Attached File:</span>
                                <a href="#" id="stdModalStudentDownloadBtn" class="badge bg-white text-primary border border-primary-subtle text-decoration-none py-1 px-2 fw-semibold" download>
                                    <i class="bi bi-download me-1"></i><span id="stdModalStudentFileName">Attachment</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Resolution Box (Shown when resolved or when comment exists) --}}
                    <div id="stdModalResolutionSection" class="border border-success rounded-3 p-3 mb-2 bg-success-subtle bg-opacity-25 d-none">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-success-subtle">
                            <div class="d-flex align-items-center gap-2 text-success fw-bold">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <span>Official Resolution & Solution</span>
                            </div>
                            <div class="smaller text-muted">
                                Resolved on: <span class="fw-semibold text-dark" id="stdModalResolvedDate">—</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="smaller text-muted fw-bold text-uppercase d-block mb-1">Resolution Remarks / Solution Details:</label>
                            <div class="p-3 bg-white rounded border border-success-subtle small text-dark" id="stdModalComment" style="white-space: pre-wrap; line-height: 1.5; font-size: 13px;"></div>
                        </div>

                        {{-- Attachment / File Download Block --}}
                        <div id="stdModalAttachmentWrap" class="d-none">
                            <label class="smaller text-muted fw-bold text-uppercase d-block mb-1">Resolution Attachment / Supporting Document:</label>
                            <div class="d-flex align-items-center justify-content-between p-2 px-3 bg-white border border-success-subtle rounded">
                                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                    <i class="bi bi-file-earmark-check-fill text-success fs-4"></i>
                                    <div class="text-truncate">
                                        <div class="fw-bold text-dark small text-truncate" id="stdModalFileName">Attachment</div>
                                        <div class="smaller text-muted">Provided by DSM support</div>
                                    </div>
                                </div>
                                <a href="#" id="stdModalDownloadBtn" class="btn btn-sm btn-success px-3 fw-semibold text-nowrap flex-shrink-0" download>
                                    <i class="bi bi-download me-1"></i> Download File
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- In-progress Notice if not yet resolved --}}
                    <div id="stdModalPendingNotice" class="border rounded-3 p-3 bg-light text-center d-none">
                        <i class="bi bi-clock-history text-primary fs-3 d-block mb-1"></i>
                        <h6 class="fw-bold mb-1 text-dark" id="stdModalPendingTitle">Ticket Under Processing</h6>
                        <p class="small text-muted mb-0">Your query is currently being handled by the DSM support team. Once addressed, the official response and any downloadable files will be posted right here.</p>
                    </div>
                </div>

                {{-- Error Container --}}
                <div id="stdModalError" class="alert alert-danger d-none py-2 px-3 small my-3" role="alert">
                    Failed to fetch ticket details. Please try again.
                </div>
            </div>

            <div class="modal-footer bg-light py-2 px-3 border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-danger fw-semibold" id="modalDeleteBtn" onclick="deleteTicketFromCurrentModal()">
                    <i class="bi bi-trash3 me-1"></i>Delete Ticket
                </button>
                <button type="button" class="btn btn-sm btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Confirm Delete Modal --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-2 d-block"></i>
                <h6 class="fw-bold mb-1 text-dark">Delete Ticket?</h6>
                <p class="small text-muted mb-3">Are you sure you want to delete ticket <strong id="deleteTktIdSpan"></strong>? This action cannot be undone.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-danger px-3 fw-bold" id="confirmDeleteSubmitBtn" onclick="submitDeleteTicket()">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="deleteSpinner"></span>
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= SCOPED COMPACT STYLING ================= --}}
<style>
    .tickets-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 6px 4px 28px;
    }

    /* Alerts and Close Button Positioning */
    .alert-dismissible {
        position: relative !important;
        padding-right: 48px !important;
    }
    .alert-dismissible .btn-close {
        position: absolute !important;
        top: 13px !important;
        right: 14px !important;
        margin: 0 !important;
        padding: 6px !important;
        font-size: 11px !important;
        opacity: 0.65;
        box-shadow: none !important;
    }
    .alert-dismissible.d-flex.align-items-center .btn-close {
        top: 50% !important;
        transform: translateY(-50%) !important;
    }
    .alert-dismissible .btn-close:hover {
        opacity: 1 !important;
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
        transition: transform 0.15s ease;
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
    .btn-oval-action {
        background: #ffffff;
        border: 1.5px solid #000000;
        border-radius: 9999px;
        padding: 3px 12px;
        font-size: 11.5px;
        font-weight: 700;
        color: #0f172a;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        white-space: nowrap;
    }
    .btn-oval-action:hover {
        background: #0f172a;
        color: #ffffff;
        transform: translateY(-1px);
    }
    .btn-oval-action.btn-oval-success {
        border-color: #16a34a;
        color: #15803d;
        background: #f0fdf4;
    }
    .btn-oval-action.btn-oval-success:hover {
        background: #16a34a;
        color: #ffffff;
    }
    .btn-oval-action.btn-oval-danger {
        border-color: #ef4444;
        color: #dc2626;
        background: #fef2f2;
        padding: 3px 9px;
    }
    .btn-oval-action.btn-oval-danger:hover {
        background: #ef4444;
        color: #ffffff;
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

    /* 2. MIDDLE CARD: MY TICKETS & RAISE FORM */
    .my-tickets-card {
        background: #eef6fc;
        border: 1px solid #d0e4f5;
        border-radius: 16px;
        padding: 18px 20px 20px;
    }
    .my-tickets-header {
        margin-bottom: 12px;
    }
    .my-tickets-title {
        font-size: 20px;
        font-weight: 800;
        color: #0c4a6e;
        margin-bottom: 2px;
        letter-spacing: -0.01em;
    }
    .my-tickets-sub {
        font-size: 12.5px;
        color: #0369a1;
        margin-bottom: 0;
    }

    .raise-ticket-inner {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 22px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    }
    .raise-ticket-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }
    .self-service-tag {
        font-size: 11.5px;
        color: #64748b;
    }
    .field-label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }
    .clean-select,
    .clean-textarea {
        background: #fafbfc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13.5px;
        color: #0f172a;
        padding: 8px 12px;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .clean-select:focus,
    .clean-textarea:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
        background: #ffffff;
        outline: none;
    }
    .clean-textarea {
        resize: vertical;
        min-height: 70px;
    }
    .char-hint {
        font-size: 11px;
        color: #94a3b8;
    }
    .student-meta-hint {
        font-size: 12px;
        color: #64748b;
    }
    .btn-raise-red {
        background: #c5222d;
        color: #ffffff;
        font-weight: 700;
        font-size: 13px;
        border: none;
        border-radius: 8px;
        padding: 7px 22px;
        box-shadow: 0 2px 6px rgba(197, 34, 45, 0.25);
        transition: background 0.15s, transform 0.15s;
    }
    .btn-raise-red:hover {
        background: #a81923;
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* 3. BOTTOM: LARGE RECTANGLE BOX */
    .sample-rect-box {
        background: var(--bg-card, #ffffff);
        border: 2px solid #000000;
        border-radius: 16px;
        padding: 16px 20px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }
    .sample-rect-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1.5px solid #e2e8f0;
        margin-bottom: 12px;
    }
    .sample-rect-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary, #0f172a);
    }
    .sample-rect-badge {
        font-size: 11.5px;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
        padding: 3px 10px;
        border-radius: 10px;
    }

    /* Table within bottom rectangle */
    .sample-table {
        font-size: 13px;
    }
    .sample-table th {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 12px;
    }
    .sample-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .tkt-code {
        font-family: monospace;
        font-weight: 700;
        color: #0284c7;
    }
    .cat-pill {
        background: #f1f5f9;
        color: #334155;
        font-size: 11.5px;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 600;
    }
    .sub-type-text {
        max-width: 220px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
        color: #1e293b;
    }
    .query-preview {
        max-width: 260px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #64748b;
    }
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
    .status-review {
        background: #fef3c7;
        color: #92400e;
    }
    .status-progress {
        background: #e0f2fe;
        color: #0369a1;
    }
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

    /* Empty state dummy lines matching user hand sketch */
    .empty-lines-wrap {
        padding: 24px 10px;
    }
    .dummy-line {
        height: 2px;
        background: #000000;
        margin: 18px 0;
        border-radius: 2px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-pills-row {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .stat-pill {
            padding: 6px 10px;
        }
        .stat-pill-label { font-size: 12px; }
        .stat-pill-val { font-size: 16px; }
        .my-tickets-card { padding: 14px; }
        .raise-ticket-inner { padding: 14px; }
    }

    /* Dark Mode Support */
    html.dark .stat-pill,
    html.dark .raise-ticket-inner,
    html.dark .sample-rect-box {
        background: #1e293b;
        border-color: #475569;
    }
    html.dark .stat-pill-label,
    html.dark .stat-pill-val,
    html.dark .raise-ticket-title,
    html.dark .sample-rect-title,
    html.dark .sub-type-text {
        color: #f1f5f9;
    }
    html.dark .my-tickets-card {
        background: #0f2744;
        border-color: #1e3a8a;
    }
    html.dark .my-tickets-title { color: #bae6fd; }
    html.dark .my-tickets-sub { color: #7dd3fc; }
    html.dark .clean-select,
    html.dark .clean-textarea {
        background: #0f172a;
        border-color: #334155;
        color: #f1f5f9;
    }
    html.dark .sample-table th {
        background: #0f172a;
        color: #94a3b8;
        border-color: #334155;
    }
    html.dark .sample-table td {
        border-color: #334155;
    }
    html.dark .dummy-line {
        background: #64748b;
    }
</style>

{{-- ================= DYNAMIC DROPDOWN SCRIPT ================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subTypesGrouped = @json($groupedSubTypes);
        const mainTypeSelect = document.getElementById('tkt_main_type');
        const subTypeSelect = document.getElementById('tkt_head_id');
        const reasonInput = document.getElementById('std_tkt_query');
        const charCounter = document.getElementById('charCounter');
        const ticketForm = document.getElementById('ticketRaiseForm');
        const submitBtn = document.getElementById('submitBtn');

        function populateSubTypes(mainType, selectedHeadId = '') {
            subTypeSelect.innerHTML = '';

            const list = subTypesGrouped[mainType] || [];
            if (list.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No sub-types found';
                subTypeSelect.appendChild(opt);
                subTypeSelect.disabled = true;
                return;
            }

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.disabled = true;
            defaultOpt.selected = !selectedHeadId;
            defaultOpt.textContent = 'Select specific sub-type';
            subTypeSelect.appendChild(defaultOpt);

            list.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.tkt_head_id;
                opt.textContent = `${item.tkt_sub_type}`;
                if (selectedHeadId && selectedHeadId === item.tkt_head_id) {
                    opt.selected = true;
                }
                subTypeSelect.appendChild(opt);
            });

            subTypeSelect.disabled = false;
        }

        mainTypeSelect.addEventListener('change', function () {
            populateSubTypes(this.value);
            subTypeSelect.focus();
        });

        const initialMain = "{{ old('tkt_main_type') }}";
        const initialHeadId = "{{ old('tkt_head_id') }}";
        if (initialMain) {
            populateSubTypes(initialMain, initialHeadId);
        }

        function updateCharCount() {
            const len = reasonInput.value.length;
            charCounter.textContent = `${len} / 500`;
            if (len >= 480) {
                charCounter.style.color = '#ef4444';
            } else {
                charCounter.style.color = '#94a3b8';
            }
        }
        reasonInput.addEventListener('input', updateCharCount);
        updateCharCount();

        ticketForm.addEventListener('submit', function (e) {
            let isValid = true;
            const queryVal = reasonInput.value.trim();
            const queryError = document.getElementById('queryError');

            if (!mainTypeSelect.value) {
                mainTypeSelect.classList.add('is-invalid');
                isValid = false;
            } else {
                mainTypeSelect.classList.remove('is-invalid');
            }

            if (!subTypeSelect.value) {
                subTypeSelect.classList.add('is-invalid');
                isValid = false;
            } else {
                subTypeSelect.classList.remove('is-invalid');
            }

            if (queryVal.length === 0) {
                reasonInput.classList.add('is-invalid');
                if (queryError) {
                    queryError.classList.remove('d-none');
                }
                reasonInput.focus();
                isValid = false;
            } else {
                reasonInput.classList.remove('is-invalid');
                if (queryError) {
                    queryError.classList.add('d-none');
                }
            }

            if (!isValid) {
                e.preventDefault();
                return false;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        });

        reasonInput.addEventListener('input', function () {
            if (this.value.trim().length > 0) {
                this.classList.remove('is-invalid');
                const queryError = document.getElementById('queryError');
                if (queryError) queryError.classList.add('d-none');
            }
        });

        // Clear file input button
        const fileInput = document.getElementById('std_tkt_file');
        const clearFileBtn = document.getElementById('clearFileBtn');
        if (fileInput && clearFileBtn) {
            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    clearFileBtn.classList.remove('d-none');
                } else {
                    clearFileBtn.classList.add('d-none');
                }
            });
            clearFileBtn.addEventListener('click', function () {
                fileInput.value = '';
                clearFileBtn.classList.add('d-none');
            });
        }
    });

    // Global Modal Opener for Student
    window.openStudentSolutionModal = function (ticketId) {
        const modalEl = document.getElementById('studentDetailModal');
        if (!modalEl) return;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        const loadingEl = document.getElementById('stdModalLoading');
        const contentEl = document.getElementById('stdModalContent');
        const errorEl = document.getElementById('stdModalError');
        const resSection = document.getElementById('stdModalResolutionSection');
        const pendingNotice = document.getElementById('stdModalPendingNotice');
        const attachWrap = document.getElementById('stdModalAttachmentWrap');
        const stdAttachWrap = document.getElementById('stdModalStudentAttachmentWrap');

        loadingEl.classList.remove('d-none');
        contentEl.classList.add('d-none');
        errorEl.classList.add('d-none');
        if (stdAttachWrap) stdAttachWrap.classList.add('d-none');

        document.getElementById('stdModalTktId').textContent = ticketId;

        fetch(`{{ url('visa/ticket-details') }}/${encodeURIComponent(ticketId)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            loadingEl.classList.add('d-none');
            if (!data.success || !data.ticket) {
                errorEl.classList.remove('d-none');
                return;
            }

            const t = data.ticket;
            contentEl.classList.remove('d-none');

            document.getElementById('stdModalTktId').textContent = t.tkt_id;
            document.getElementById('stdModalCategoryTitle').textContent = t.category || 'Ticket Details';
            document.getElementById('stdModalCategory').textContent = t.category || 'N/A';
            document.getElementById('stdModalSubType').textContent = t.sub_type ? `• ${t.sub_type}` : '';
            document.getElementById('stdModalRaisedDate').textContent = t.raised_date || '—';
            document.getElementById('stdModalQuery').textContent = t.query || '—';

            // Student's own attached file
            if (stdAttachWrap) {
                if (t.has_std_file && t.std_file_url) {
                    stdAttachWrap.classList.remove('d-none');
                    document.getElementById('stdModalStudentFileName').textContent = t.std_tkt_file_name || 'Download Attachment';
                    document.getElementById('stdModalStudentDownloadBtn').href = t.std_file_url;
                } else {
                    stdAttachWrap.classList.add('d-none');
                }
            }

            const chip = document.getElementById('stdModalStatusChip');
            chip.className = `status-chip ${t.status_class || ''}`;
            chip.textContent = t.status_label || ('Status ' + t.std_tkt_status);

            const isResolved = parseInt(t.std_tkt_status) === 3;
            const hasComment = Boolean(t.emp_tkt_comment && t.emp_tkt_comment.trim().length > 0);

            if (isResolved || hasComment) {
                resSection.classList.remove('d-none');
                pendingNotice.classList.add('d-none');

                document.getElementById('stdModalResolvedDate').textContent = t.emp_timestamp || 'Recently';
                document.getElementById('stdModalComment').textContent = t.emp_tkt_comment || 'No comment provided.';

                if (t.has_file && t.file_url) {
                    attachWrap.classList.remove('d-none');
                    document.getElementById('stdModalFileName').textContent = t.emp_tkt_file_name || 'Download Attachment';
                    document.getElementById('stdModalDownloadBtn').href = t.file_url;
                } else {
                    attachWrap.classList.add('d-none');
                }
            } else {
                resSection.classList.add('d-none');
                pendingNotice.classList.remove('d-none');
                const pendingTitle = document.getElementById('stdModalPendingTitle');
                if (parseInt(t.std_tkt_status) === 1) {
                    pendingTitle.textContent = 'Ticket is Pending Review';
                } else if (parseInt(t.std_tkt_status) === 2) {
                    pendingTitle.textContent = 'Ticket is In Process';
                } else {
                    pendingTitle.textContent = 'Ticket Raised Successfully';
                }
            }
        })
        .catch(err => {
            console.error("Error loading ticket details:", err);
            loadingEl.classList.add('d-none');
            errorEl.classList.remove('d-none');
        });
    };

    // Global Delete Handlers
    let ticketToDelete = null;

    window.confirmDeleteTicket = function (ticketId) {
        ticketToDelete = ticketId;
        document.getElementById('deleteTktIdSpan').textContent = ticketId;
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteConfirmModal'));
        modal.show();
    };

    window.deleteTicketFromCurrentModal = function () {
        const tktId = document.getElementById('stdModalTktId').textContent.trim();
        if (!tktId) return;
        const detailModal = bootstrap.Modal.getInstance(document.getElementById('studentDetailModal'));
        if (detailModal) detailModal.hide();
        window.confirmDeleteTicket(tktId);
    };

    window.submitDeleteTicket = function () {
        if (!ticketToDelete) return;
        const btn = document.getElementById('confirmDeleteSubmitBtn');
        const spinner = document.getElementById('deleteSpinner');
        btn.disabled = true;
        spinner.classList.remove('d-none');

        fetch(`{{ url('visa/ticket-delete') }}/${encodeURIComponent(ticketToDelete)}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Failed to delete ticket.');
            }
            return data;
        })
        .then(data => {
            window.location.reload();
        })
        .catch(err => {
            btn.disabled = false;
            spinner.classList.add('d-none');
            alert(err.message || 'Error deleting ticket. Please try again.');
        });
    };
</script>
@endsection
