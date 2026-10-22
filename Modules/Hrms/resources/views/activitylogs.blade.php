@extends('layouts.app')

@section('page_title', 'Activity Logs')

@section('content')
<div class="cdoe-dashboard-wrap">

    {{-- ================= 1. HEADER HERO BANNER ================= --}}
    <div class="dash-hero-card">
        <div class="dash-hero-content">
            <div class="dash-hero-badge">
                <i class="bi bi-shield-lock-fill me-1"></i> Security & Audit Trail • Institutional Workspace
            </div>
            <h1 class="dash-hero-title">
                Activity & Audit <span class="dash-hero-name">Logs</span>
            </h1>
            <p class="dash-hero-sub">
                Track and inspect each and every user activity in real time. Monitor user authentications, role switches, employee creation, and administrative events across the university portal.
            </p>

            <div class="dash-hero-actions">
                <button type="button" class="dash-hero-btn primary" onclick="refreshLogs()">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Refresh Live Feed</span>
                </button>
                <a href="{{ route('hrms.empcreation') }}" class="dash-hero-btn outline">
                    <i class="bi bi-person-plus"></i>
                    <span>New Employee</span>
                </a>
                <a href="{{ route('hrms.empsearch') }}" class="dash-hero-btn outline">
                    <i class="bi bi-people"></i>
                    <span>Employee Search</span>
                </a>
            </div>
        </div>

        <div class="dash-hero-aside">
            <div class="dash-session-pill">
                <span class="dash-pulse-dot" style="background: #10b981;"></span>
                <span>Active Telemetry • Live</span>
            </div>
        </div>
    </div>

    {{-- ================= 2. LIVE STATISTICS CARDS ================= --}}
    <div class="dash-stat-grid">

        {{-- Total Activities --}}
        <div class="dash-stat-card blue">
            <div class="dash-stat-header">
                <div class="dash-stat-icon blue">
                    <i class="bi bi-journal-text"></i>
                </div>
                <span class="dash-stat-tag blue">All-Time</span>
            </div>
            <div class="dash-stat-val" id="statTotal">{{ number_format($stats['total'] ?? 0) }}</div>
            <div class="dash-stat-label">Total Activities Recorded</div>
            <div class="dash-stat-footer">
                <i class="bi bi-check-circle-fill text-success me-1"></i> Continuous institutional audit log
            </div>
        </div>

        {{-- Today's Activities --}}
        <div class="dash-stat-card green">
            <div class="dash-stat-header">
                <div class="dash-stat-icon green">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <span class="dash-stat-tag green">Today</span>
            </div>
            <div class="dash-stat-val" id="statToday">{{ number_format($stats['today'] ?? 0) }}</div>
            <div class="dash-stat-label">Activities Today</div>
            <div class="dash-stat-footer">
                <i class="bi bi-clock-history text-info me-1"></i> Recorded in current calendar day
            </div>
        </div>

        {{-- Auth & Security Events --}}
        <div class="dash-stat-card amber">
            <div class="dash-stat-header">
                <div class="dash-stat-icon amber">
                    <i class="bi bi-shield-check"></i>
                </div>
                <span class="dash-stat-tag amber">Security</span>
            </div>
            <div class="dash-stat-val" id="statAuth">{{ number_format($stats['auth_today'] ?? 0) }}</div>
            <div class="dash-stat-label">Auth Events Today</div>
            <div class="dash-stat-footer">
                <i class="bi bi-key-fill text-warning me-1"></i> Logins, Logouts & Role Switches
            </div>
        </div>

        {{-- Active Users --}}
        <div class="dash-stat-card crimson">
            <div class="dash-stat-header">
                <div class="dash-stat-icon crimson">
                    <i class="bi bi-people-fill"></i>
                </div>
                <span class="dash-stat-tag crimson">Personnel</span>
            </div>
            <div class="dash-stat-val" id="statUsers">{{ number_format($stats['active_users'] ?? 0) }}</div>
            <div class="dash-stat-label">Active Users Today</div>
            <div class="dash-stat-footer">
                <i class="bi bi-person-check-fill text-danger me-1"></i> Unique operators interacting
            </div>
        </div>

    </div>

    {{-- ================= 3. SEARCH & FILTER TOOLBAR ================= --}}
    <div class="ss-toolbar-card">
        <div class="row g-2 align-items-center">

            {{-- Live Search Input --}}
            <div class="col-lg-4 col-md-12">
                <div class="ss-search-input-wrap">
                    <span class="ss-search-icon">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           id="liveSearchInput"
                           class="ss-search-input"
                           placeholder="Search by user code, name, action, IP, description... (Press /)"
                           value="{{ $q ?? '' }}"
                           autocomplete="off"
                           spellcheck="false">
                    <span class="ss-search-spinner" id="searchSpinner" style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                    </span>
                    <button type="button"
                            class="ss-clear-btn"
                            id="clearSearchBtn"
                            style="{{ !empty($q) ? 'display: flex;' : 'display: none;' }}"
                            title="Clear search">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <span class="ss-kbd-hint d-none d-xl-inline">/</span>
                </div>
            </div>

            {{-- Filter: Module --}}
            <div class="col-lg-2 col-sm-6">
                <div class="ss-select-wrap">
                    <select id="filterModule" class="form-select ss-select">
                        <option value="all">All Modules</option>
                        <option value="AUTH" {{ ($module ?? '') === 'AUTH' ? 'selected' : '' }}>AUTH</option>
                        <option value="HRMS" {{ ($module ?? '') === 'HRMS' ? 'selected' : '' }}>HRMS</option>
                        <option value="ADMISSIONS" {{ ($module ?? '') === 'ADMISSIONS' ? 'selected' : '' }}>ADMISSIONS</option>
                        <option value="EXAMINATIONS" {{ ($module ?? '') === 'EXAMINATIONS' ? 'selected' : '' }}>EXAMINATIONS</option>
                        <option value="SYSTEM" {{ ($module ?? '') === 'SYSTEM' ? 'selected' : '' }}>SYSTEM</option>
                    </select>
                </div>
            </div>

            {{-- Filter: Action --}}
            <div class="col-lg-2 col-sm-6">
                <div class="ss-select-wrap">
                    <select id="filterAction" class="form-select ss-select">
                        <option value="all">All Actions</option>
                        <option value="LOGIN" {{ ($action ?? '') === 'LOGIN' ? 'selected' : '' }}>LOGIN</option>
                        <option value="LOGOUT" {{ ($action ?? '') === 'LOGOUT' ? 'selected' : '' }}>LOGOUT</option>
                        <option value="SWITCH_ROLE" {{ ($action ?? '') === 'SWITCH_ROLE' ? 'selected' : '' }}>SWITCH_ROLE</option>
                        <option value="CREATE_EMPLOYEE" {{ ($action ?? '') === 'CREATE_EMPLOYEE' ? 'selected' : '' }}>CREATE_EMPLOYEE</option>
                        <option value="SEARCH" {{ ($action ?? '') === 'SEARCH' ? 'selected' : '' }}>SEARCH</option>
                    </select>
                </div>
            </div>

            {{-- Filter: Date --}}
            <div class="col-lg-2 col-sm-6">
                <div class="ss-select-wrap">
                    <select id="filterDate" class="form-select ss-select">
                        <option value="all" {{ ($dateFilter ?? '') === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ ($dateFilter ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ ($dateFilter ?? '') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="7days" {{ ($dateFilter ?? '') === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ ($dateFilter ?? '') === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                </div>
            </div>

            {{-- Reset & Per Page --}}
            <div class="col-lg-2 col-sm-6 d-flex gap-2">
                <select id="filterPerPage" class="form-select ss-select" style="min-width: 80px;">
                    <option value="15" {{ ($perPage ?? 20) == 15 ? 'selected' : '' }}>15 / page</option>
                    <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20 / page</option>
                    <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100 / page</option>
                </select>

                <button type="button" id="resetFiltersBtn" class="ss-reset-btn" title="Reset all filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>

        </div>
    </div>

    {{-- ================= 4. ACTIVITY LOGS TABLE ================= --}}
    <div class="dash-card">
        <div class="dash-card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="dash-card-title mb-1">
                    <i class="bi bi-list-check me-2 text-primary"></i>Audit Log Feed
                </h4>
                <div class="text-muted small" id="resultsCountText">
                    Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }} activities
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="dash-btn-sm" onclick="refreshLogs()">
                    <i class="bi bi-arrow-repeat me-1"></i> Refresh
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table ss-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width: 140px;">Timestamp</th>
                        <th style="min-width: 180px;">User & Role</th>
                        <th style="min-width: 140px;">Module & Event</th>
                        <th style="min-width: 280px;">Description</th>
                        <th style="min-width: 170px;">Client IP & Device</th>
                        <th style="min-width: 100px;">Status</th>
                        <th class="text-end" style="min-width: 90px;">Details</th>
                    </tr>
                </thead>
                <tbody id="activityTableBody">
                    @include('hrms::partials.activity-rows', ['logs' => $logs])
                </tbody>
            </table>
        </div>

        {{-- Pagination Container --}}
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2" id="paginationContainer">
            @if ($logs->count() > 0 && $logs->hasPages())
                {{ $logs->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>

</div>

{{-- ================= 5. INSPECT DETAIL MODAL ================= --}}
{{-- ================= 5. INSPECT DETAIL MODAL ================= --}}
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ss-modal-content">

            {{-- Modal Hero Header --}}
            <div class="ss-modal-hero position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="ss-hero-avatar">
                        <i class="bi bi-shield-shaded"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="ss-hero-name">Activity Log Details</span>
                            <span class="ss-hero-badge" id="modalLogId">Event #--</span>
                            <span id="modalStatusBadge"></span>
                        </div>
                        <div class="ss-hero-sub" id="modalHeroSub">
                            Audit telemetry snapshot • Verified institutional event
                        </div>
                    </div>
                </div>

                {{-- Hero Quick Stats Ribbon --}}
                <div class="ss-hero-ribbon">
                    <div class="ss-ribbon-item">
                        <span class="ss-ribbon-label">OPERATOR CODE</span>
                        <span class="ss-ribbon-val font-monospace" id="modalRibbonUser">-</span>
                    </div>
                    <div class="ss-ribbon-item">
                        <span class="ss-ribbon-label">ACTION / EVENT</span>
                        <span class="ss-ribbon-val font-monospace" id="modalRibbonAction">-</span>
                    </div>
                    <div class="ss-ribbon-item">
                        <span class="ss-ribbon-label">OCCURRED AT</span>
                        <span class="ss-ribbon-val" id="modalRibbonTime">-</span>
                    </div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body ss-modal-body p-4">
                <div class="row g-3">

                    {{-- Left Box: Identity & Security Context --}}
                    <div class="col-md-6">
                        <div class="ss-section-box">
                            <div class="ss-box-title">
                                <i class="bi bi-person-badge-fill me-2"></i>Actor & Role Context
                            </div>
                            <div class="ss-kv-list">
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">User Code</span>
                                    <span class="ss-kv-val font-monospace text-primary fw-bold" id="modalUserCode">-</span>
                                </div>
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">User / Role</span>
                                    <span class="ss-kv-val" id="modalUserRole">-</span>
                                </div>
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">Module</span>
                                    <span class="ss-kv-val" id="modalModule">-</span>
                                </div>
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">Action Type</span>
                                    <span class="ss-kv-val font-monospace fw-bold" id="modalAction">-</span>
                                </div>
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">Execution Status</span>
                                    <span class="ss-kv-val" id="modalStatus">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Box: Network & Environment --}}
                    <div class="col-md-6">
                        <div class="ss-section-box">
                            <div class="ss-box-title">
                                <i class="bi bi-hdd-network-fill me-2"></i>Network & Client Info
                            </div>
                            <div class="ss-kv-list">
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">Client IP</span>
                                    <span class="ss-kv-val font-monospace" id="modalIp">-</span>
                                </div>
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">Timestamp (Full)</span>
                                    <span class="ss-kv-val" id="modalTimestamp">-</span>
                                </div>
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">Device / Platform</span>
                                    <span class="ss-kv-val" id="modalDevice">-</span>
                                </div>
                                <div class="ss-kv-row">
                                    <span class="ss-kv-key">Telemetry State</span>
                                    <span class="ss-kv-val text-success">
                                        <i class="bi bi-shield-check me-1"></i>Logged & Indexed
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Full-width: User Agent string --}}
                    <div class="col-12">
                        <div class="ss-section-box">
                            <div class="ss-box-title">
                                <i class="bi bi-laptop me-2"></i>User Agent Header
                            </div>
                            <div class="p-2 rounded border small font-monospace text-break" id="modalUserAgent" style="background: var(--bg-hover-row); border-color: var(--border-subtle) !important; color: var(--text-secondary); font-size: 11px; line-height: 1.5;">
                                -
                            </div>
                        </div>
                    </div>

                    {{-- Full-width: Event Narrative / Description --}}
                    <div class="col-12">
                        <div class="ss-section-box">
                            <div class="ss-box-title">
                                <i class="bi bi-chat-left-text-fill me-2"></i>Event Description
                            </div>
                            <div class="p-3 rounded border" id="modalDescription" style="background: var(--bg-hover-row); border-left: 4px solid var(--theme-primary) !important; border-color: var(--border-subtle); color: var(--text-primary); font-size: 13.5px; font-weight: 500; line-height: 1.5;">
                                -
                            </div>
                        </div>
                    </div>

                    {{-- Full-width: Event Payload JSON --}}
                    <div class="col-12">
                        <div class="ss-section-box">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: var(--border-subtle) !important;">
                                <div class="ss-box-title mb-0 border-bottom-0 pb-0">
                                    <i class="bi bi-code-slash me-2"></i>Event Payload & Context
                                </div>
                                <button type="button" class="ss-copy-btn" id="copyPayloadBtn" title="Copy JSON">
                                    <i class="bi bi-clipboard me-1"></i> Copy JSON
                                </button>
                            </div>
                            <pre class="p-3 rounded border font-monospace small mb-0" id="modalPayload" style="background: var(--bg-main); border-color: var(--border-subtle) !important; max-height: 220px; overflow-y: auto; color: var(--text-primary); font-size: 12px; line-height: 1.5;">None</pre>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer ss-modal-footer d-flex justify-content-end align-items-center">
                <button type="button" class="ss-close-btn" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Dashboard & Live Search toolbar styles */
.cdoe-dashboard-wrap {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.ss-toolbar-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 16px;
    padding: 16px 20px;
    box-shadow: var(--card-shadow);
}

.ss-search-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.ss-search-icon {
    position: absolute;
    left: 14px;
    color: var(--text-muted);
    font-size: 15px;
    pointer-events: none;
}

.ss-search-input {
    width: 100%;
    height: 42px;
    padding: 0 44px 0 40px;
    border-radius: 10px;
    border: 1px solid var(--border-subtle);
    background: var(--bg-main);
    color: var(--text-primary);
    font-size: 13px;
    transition: all 0.2s ease;
}

.ss-search-input:focus {
    border-color: var(--theme-primary);
    outline: none;
    box-shadow: 0 0 0 3px var(--theme-glow);
}

.ss-kbd-hint {
    position: absolute;
    right: 12px;
    padding: 2px 7px;
    font-size: 11px;
    color: var(--text-muted);
    background: var(--bg-hover-row);
    border: 1px solid var(--border-subtle);
    border-radius: 4px;
    font-family: inherit;
    pointer-events: none;
}

.ss-clear-btn {
    position: absolute;
    right: 36px;
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 50%;
}
.ss-clear-btn:hover { color: var(--theme-primary); }

.ss-select {
    height: 42px;
    border-radius: 10px;
    border: 1px solid var(--border-subtle);
    background-color: var(--bg-main);
    color: var(--text-primary);
    font-size: 13px;
    transition: all 0.2s ease;
}
.ss-select:focus {
    border-color: var(--theme-primary);
    box-shadow: 0 0 0 3px var(--theme-glow);
}

.ss-reset-btn {
    height: 42px;
    width: 42px;
    border-radius: 10px;
    border: 1px solid var(--border-subtle);
    background: var(--bg-main);
    color: var(--text-secondary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.ss-reset-btn:hover {
    background: var(--bg-hover-row);
    color: var(--theme-primary);
    border-color: var(--theme-primary);
}

/* Table styles */
.ss-table {
    width: 100%;
    margin-bottom: 0;
    color: var(--text-secondary);
}
.ss-table thead th {
    background: var(--bg-hover-row);
    color: var(--text-muted);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-subtle);
}
.ss-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-subtle);
    background: transparent;
}
.ss-table tbody tr:hover td {
    background: var(--bg-hover-row);
}

.ss-avatar-mini {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    flex-shrink: 0;
}

.ss-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
}
.ss-status-active {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.ss-status-inactive {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}
.ss-dot-pulse {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 6px #10b981;
}

.ss-view-btn {
    border: 1px solid var(--border-subtle);
    background: var(--bg-card);
    color: var(--text-secondary);
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
    cursor: pointer;
}
.ss-view-btn:hover {
    background: var(--theme-primary);
    color: #fff;
    border-color: var(--theme-primary);
    box-shadow: 0 2px 8px var(--theme-glow);
}

.dash-btn-sm {
    border: 1px solid var(--border-subtle);
    background: var(--bg-hover-row);
    color: var(--text-primary);
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.dash-btn-sm:hover {
    border-color: var(--theme-primary);
    color: var(--theme-primary);
}

/* Hero card styling */
.dash-hero-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 18px;
    padding: 28px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}
.dash-hero-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--theme-gradient);
}
.dash-hero-badge {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 700;
    color: var(--theme-primary);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 8px;
}
.dash-hero-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 8px;
}
.dash-hero-name {
    background: var(--theme-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.dash-hero-sub {
    font-size: 13px;
    color: var(--text-secondary);
    max-width: 650px;
    margin-bottom: 18px;
}
.dash-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.dash-hero-btn {
    padding: 7px 16px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
}
.dash-hero-btn.primary {
    background: var(--theme-gradient);
    color: #fff;
    box-shadow: 0 4px 12px var(--theme-glow);
}
.dash-hero-btn.outline {
    background: var(--bg-card);
    color: var(--text-primary);
    border: 1px solid var(--border-subtle);
}
.dash-hero-btn.outline:hover {
    border-color: var(--theme-primary);
    color: var(--theme-primary);
}

/* Stat Grid */
.dash-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.dash-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 14px;
    padding: 20px;
    box-shadow: var(--card-shadow);
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease;
}
.dash-stat-card:hover { transform: translateY(-2px); }
.dash-stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.dash-stat-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.dash-stat-icon.blue { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.dash-stat-icon.green { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.dash-stat-icon.amber { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.dash-stat-icon.crimson { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

.dash-stat-tag {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 8px;
    border-radius: 6px;
}
.dash-stat-tag.blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.dash-stat-tag.green { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.dash-stat-tag.amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.dash-stat-tag.purple { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
.dash-stat-tag.crimson { background: rgba(239, 68, 68, 0.12); color: #ef4444; }

.dash-stat-val {
    font-size: 26px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 6px;
}
.dash-stat-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 10px;
}
.dash-stat-footer {
    font-size: 11px;
    color: var(--text-muted);
    border-top: 1px solid var(--border-subtle);
    padding-top: 8px;
}

.dash-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    overflow: hidden;
}
.dash-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-subtle);
}
.dash-card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
}

/* Modal styling matching LMS and EmpSearch */
.ss-modal-content {
    border: 1px solid var(--border-card);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    background: var(--bg-card);
}
.ss-modal-hero {
    background: var(--theme-gradient);
    color: #fff;
    padding: 22px 24px 18px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
.ss-hero-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.65);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.ss-hero-name {
    font-size: 19px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -0.2px;
}
.ss-hero-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.22);
    color: #fff;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    border: 1px solid rgba(255, 255, 255, 0.35);
}
.ss-hero-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(16, 185, 129, 0.25);
    color: #ecfdf5;
    border: 1px solid rgba(16, 185, 129, 0.45);
    padding: 2px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.ss-hero-sub {
    color: rgba(255, 255, 255, 0.85);
    font-size: 12.5px;
    margin-top: 3px;
}
.ss-hero-ribbon {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 10px;
    background: rgba(0, 0, 0, 0.2);
    padding: 10px 14px;
    border-radius: 10px;
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, 0.12);
}
.ss-ribbon-item { display: flex; flex-direction: column; }
.ss-ribbon-label { font-size: 10px; font-weight: 700; letter-spacing: 0.6px; color: rgba(255, 255, 255, 0.7); margin-bottom: 2px; }
.ss-ribbon-val { font-size: 13.5px; font-weight: 600; color: #fff; }

.ss-modal-body {
    background: var(--bg-main);
    max-height: 72vh;
    overflow-y: auto;
}
.ss-section-box {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 12px;
    padding: 14px 16px;
    height: 100%;
    box-shadow: var(--card-shadow);
}
.ss-box-title {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: var(--theme-primary);
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border-subtle);
    display: flex;
    align-items: center;
}
.ss-kv-list { display: flex; flex-direction: column; gap: 4px; }
.ss-kv-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 4px;
    font-size: 12.5px;
    border-bottom: 1px dashed var(--border-subtle);
    transition: background 0.15s;
}
.ss-kv-row:last-child { border-bottom: none; }
.ss-kv-row:hover { background: var(--bg-hover-row); border-radius: 4px; }
.ss-kv-key { color: var(--text-muted); font-size: 12px; font-weight: 500; }
.ss-kv-val { color: var(--text-primary); font-weight: 600; text-align: right; max-width: 62%; word-break: break-word; }

.ss-copy-btn {
    background: var(--bg-hover-row);
    border: 1px solid var(--border-subtle);
    color: var(--text-secondary);
    cursor: pointer;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    transition: all .15s;
}
.ss-copy-btn:hover { color: var(--theme-primary); border-color: var(--theme-primary); }

.ss-modal-footer {
    background: var(--bg-card);
    border-top: 1px solid var(--border-subtle);
    padding: 12px 22px;
}
.ss-close-btn {
    background: var(--bg-elevated);
    color: var(--text-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    padding: 8px 22px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.ss-close-btn:hover { background: var(--bg-hover-row); color: var(--text-primary); border-color: var(--theme-primary); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('liveSearchInput');
    const filterModule = document.getElementById('filterModule');
    const filterAction = document.getElementById('filterAction');
    const filterDate = document.getElementById('filterDate');
    const filterPerPage = document.getElementById('filterPerPage');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const searchSpinner = document.getElementById('searchSpinner');
    const tableBody = document.getElementById('activityTableBody');
    const resultsCountText = document.getElementById('resultsCountText');
    const paginationContainer = document.getElementById('paginationContainer');

    let debounceTimer = null;
    let currentAbortController = null;

    // Global keyboard shortcut '/' to focus search
    window.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement !== searchInput && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });

    // Clear search button
    clearSearchBtn.addEventListener('click', function () {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        triggerSearch(true);
        searchInput.focus();
    });

    // Search input typing with debounce
    searchInput.addEventListener('input', function () {
        clearSearchBtn.style.display = searchInput.value.trim() ? 'flex' : 'none';
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => triggerSearch(false), 280);
    });

    // Filters change
    [filterModule, filterAction, filterDate, filterPerPage].forEach(el => {
        el.addEventListener('change', () => triggerSearch(true));
    });

    // Reset filters
    resetFiltersBtn.addEventListener('click', function () {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        filterModule.value = 'all';
        filterAction.value = 'all';
        filterDate.value = 'all';
        filterPerPage.value = '20';
        triggerSearch(true);
    });

    // Handle pagination clicks inside container
    paginationContainer.addEventListener('click', function (e) {
        const link = e.target.closest('a.page-link');
        if (link && link.href) {
            e.preventDefault();
            const url = new URL(link.href);
            const page = url.searchParams.get('page') || 1;
            triggerSearch(false, page);
        }
    });

    // Core AJAX search function
    function triggerSearch(resetPage = false, pageNumber = null) {
        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        searchSpinner.style.display = 'inline-block';

        const params = new URLSearchParams();
        const q = searchInput.value.trim();
        if (q) params.set('q', q);
        if (filterModule.value !== 'all') params.set('module', filterModule.value);
        if (filterAction.value !== 'all') params.set('action', filterAction.value);
        if (filterDate.value !== 'all') params.set('date', filterDate.value);
        if (filterPerPage.value !== '20') params.set('per_page', filterPerPage.value);

        if (!resetPage && pageNumber) {
            params.set('page', pageNumber);
        }

        const fetchUrl = `{{ route('hrms.activitylogs') }}?${params.toString()}`;

        // Update browser URL
        window.history.replaceState({}, '', fetchUrl);

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: currentAbortController.signal
        })
        .then(res => res.json())
        .then(data => {
            searchSpinner.style.display = 'none';
            if (data.success) {
                tableBody.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;
                resultsCountText.textContent = `Showing ${data.from} to ${data.to} of ${Number(data.total).toLocaleString()} activities`;

                if (data.stats) {
                    if (document.getElementById('statTotal')) document.getElementById('statTotal').textContent = Number(data.stats.total).toLocaleString();
                    if (document.getElementById('statToday')) document.getElementById('statToday').textContent = Number(data.stats.today).toLocaleString();
                    if (document.getElementById('statAuth')) document.getElementById('statAuth').textContent = Number(data.stats.auth_today).toLocaleString();
                    if (document.getElementById('statUsers')) document.getElementById('statUsers').textContent = Number(data.stats.active_users).toLocaleString();
                }
            }
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                searchSpinner.style.display = 'none';
                console.error('Activity logs fetch error:', err);
            }
        });
    }

    // Expose refresh function to window
    window.refreshLogs = function () {
        triggerSearch(false);
    };

    // Inspect Modal Handling
    const modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.inspect-log-btn');
        if (btn) {
            const logId = btn.getAttribute('data-log-id');
            fetch(`{{ url('hrms/activitylogs') }}/${logId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.log) {
                    const l = data.log;

                    // Header & Ribbon
                    document.getElementById('modalLogId').textContent = `Event #${l.id}`;
                    document.getElementById('modalRibbonUser').textContent = l.user_code || 'Guest / System';
                    document.getElementById('modalRibbonAction').textContent = `${l.module} • ${l.action}`;
                    document.getElementById('modalRibbonTime').textContent = data.created_at_human || '-';

                    // Hero status badge
                    const statusBadgeEl = document.getElementById('modalStatusBadge');
                    if (statusBadgeEl) {
                        statusBadgeEl.innerHTML = (l.status === 'success')
                            ? '<span class="ss-hero-status"><span class="ss-dot-pulse"></span> Success</span>'
                            : `<span class="ss-hero-status" style="background: rgba(239, 68, 68, 0.25); border-color: rgba(239, 68, 68, 0.45); color: #fecaca;">${l.status}</span>`;
                    }

                    // Key-value fields
                    document.getElementById('modalUserCode').textContent = l.user_code || 'Guest / System';
                    document.getElementById('modalUserRole').textContent = `${l.user_name || 'Member'} (${l.role_name || 'Role #' + (l.role_id || '-')})`;
                    document.getElementById('modalModule').innerHTML = `<span class="dash-stat-tag purple">${l.module || 'SYSTEM'}</span>`;
                    document.getElementById('modalAction').textContent = l.action || '-';
                    document.getElementById('modalStatus').innerHTML = l.status === 'success'
                        ? '<span class="ss-status-badge ss-status-active"><span class="ss-dot-pulse"></span> Success</span>'
                        : `<span class="ss-status-badge ss-status-inactive">${l.status}</span>`;

                    document.getElementById('modalTimestamp').textContent = `${data.created_at_full} (${data.created_at_human})`;
                    document.getElementById('modalIp').textContent = l.ip_address || '127.0.0.1';
                    
                    const deviceEl = document.getElementById('modalDevice');
                    if (deviceEl) {
                        deviceEl.textContent = l.device_type || 'Desktop / Web';
                    }

                    document.getElementById('modalUserAgent').textContent = l.user_agent || l.device_type || 'No user agent header available';
                    document.getElementById('modalDescription').textContent = l.description || 'No description provided.';
                    document.getElementById('modalPayload').textContent = l.payload ? JSON.stringify(l.payload, null, 2) : 'No payload recorded.';

                    modal.show();
                }
            })
            .catch(err => console.error('Inspect log error:', err));
        }
    });

    // Copy JSON Payload Button
    const copyBtn = document.getElementById('copyPayloadBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const text = document.getElementById('modalPayload').textContent;
            navigator.clipboard.writeText(text).then(() => {
                copyBtn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> Copied!';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy JSON';
                }, 2000);
            }).catch(() => {
                // Fallback copy
                const temp = document.createElement('textarea');
                temp.value = text;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                copyBtn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> Copied!';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy JSON';
                }, 2000);
            });
        });
    }
});
</script>
@endpush
