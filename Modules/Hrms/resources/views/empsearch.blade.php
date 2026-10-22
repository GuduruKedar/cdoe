@extends('layouts.app')

@section('page_title', 'Employee Search')

@section('content')

    <div class="container-fluid px-0">

        {{-- ================= SEARCH & FILTER TOOLBAR ================= --}}
        <div class="ss-toolbar-card mb-3">
            <div class="row g-2 align-items-center">
                {{-- Live Search Input --}}
                <div class="col-lg-7 col-md-12">
                    <div class="ss-search-input-wrap">
                        <span class="ss-search-icon">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text"
                               id="liveSearchInput"
                               class="ss-search-input"
                               placeholder="Search by emp code, name, email, phone, type... (Press / to search)"
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

                {{-- Filter: Type --}}
                <div class="col-lg-2 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterType" class="form-select ss-select">
                            <option value="all">All Types</option>
                            @foreach ($types as $t)
                                <option value="{{ $t }}" {{ ($empType ?? '') === $t ? 'selected' : '' }}>
                                    {{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter: Status --}}
                <div class="col-lg-2 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterStatus" class="form-select ss-select">
                            <option value="all">All Status</option>
                            <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                {{-- Reset Button --}}
                <div class="col-lg-1 col-12 text-end">
                    <button type="button"
                            class="ss-reset-btn w-100"
                            id="resetFiltersBtn"
                            title="Reset all filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span class="d-inline d-lg-none ms-1">Reset</span>
                    </button>
                </div>
            </div>

            {{-- Summary bar --}}
            <div class="ss-toolbar-meta mt-2 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="ss-meta-left d-flex align-items-center gap-2">
                    <span class="ss-result-badge">
                        Showing <strong id="countFrom">{{ $employees->firstItem() ?? 0 }}</strong> -
                        <strong id="countTo">{{ $employees->lastItem() ?? 0 }}</strong> of
                        <strong id="countTotal">{{ $employees->total() }}</strong> employees
                    </span>
                    <span id="activeFiltersBadge" class="ss-filter-pill" style="display: none;">
                        Filtered
                    </span>
                </div>
                <div class="ss-meta-right d-flex align-items-center gap-2">
                    <span class="text-muted small me-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Live Semantic Search</span>
                    <a href="{{ route('hrms.empcreation') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1" style="background: var(--theme-gradient); border-color: var(--theme-primary); font-size: 12px; font-weight: 600; text-decoration: none;">
                        <i class="bi bi-plus-lg me-1"></i> Add Employee
                    </a>
                </div>
            </div>
        </div>

        {{-- ================= TABLE CARD ================= --}}
        <div class="ss-card">

            <div class="table-responsive">
                <table class="table ss-table align-middle mb-0">
                    <thead>
                    <tr>
                        <th style="width: 15%;">Emp Code</th>
                        <th style="width: 30%;">Full Name</th>
                        <th style="width: 25%;">Official Email</th>
                        <th style="width: 15%;">Phone</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%; text-align: center;">Action</th>
                    </tr>
                    </thead>
                    <tbody id="employeeTableBody">
                        @include('hrms::partials.employee-rows', ['employees' => $employees])
                    </tbody>
                </table>
            </div>

            {{-- ================= PAGINATION ================= --}}
            <div class="ss-pagination-wrap" id="paginationWrap" style="{{ ($employees->isEmpty() || !$employees->hasPages()) ? 'display: none;' : '' }}">
                @if (method_exists($employees, 'withQueryString') && $employees->hasPages() && $employees->count() > 0)
                    {{ $employees->withQueryString()->links('pagination::bootstrap-5') }}
                @endif
            </div>

        </div>
    </div>

    {{-- ================= POPUP MODAL: MODERN EMPLOYEE PROFILE ================= --}}
    <div class="modal fade" id="employeeModal" tabindex="-1"
         aria-labelledby="employeeModalLabel" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content ss-modal-content">

                {{-- Hero Header --}}
                <div class="ss-modal-hero">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ss-hero-avatar" id="modal-emp-avatar">E</div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h4 class="ss-hero-name mb-0" id="modal-emp-name">-</h4>
                                    <span class="ss-hero-badge" id="modal-emp-type">-</span>
                                    <span class="ss-hero-status" id="modal-emp-status-badge">
                                        <span class="ss-dot-pulse"></span>
                                        <span id="modal-emp-status">-</span>
                                    </span>
                                </div>
                                <div class="ss-hero-sub font-monospace" id="modal-emp-code-sub">-</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    {{-- Quick Ribbon --}}
                    <div class="ss-hero-ribbon mt-3">
                        <div class="ss-ribbon-item">
                            <span class="ss-ribbon-label">EMPLOYEE CODE</span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="ss-ribbon-val font-monospace" id="modal-emp-code">-</span>
                                <button type="button" class="ss-copy-btn" id="copyEmpCodeBtn" title="Copy Emp Code">
                                    <i class="bi bi-copy" id="copyEmpCodeIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="ss-ribbon-item">
                            <span class="ss-ribbon-label">EMPLOYMENT TYPE</span>
                            <span class="ss-ribbon-val" id="modal-emp-type-ribbon">-</span>
                        </div>
                        <div class="ss-ribbon-item">
                            <span class="ss-ribbon-label">STATUS</span>
                            <span class="ss-ribbon-val" id="modal-emp-status-ribbon">-</span>
                        </div>
                        <div class="ss-ribbon-item">
                            <span class="ss-ribbon-label">DATE OF JOIN</span>
                            <span class="ss-ribbon-val" id="modal-emp-doj">-</span>
                        </div>
                    </div>
                </div>

                {{-- Modal Body: 2 Clean Columns --}}
                <div class="modal-body ss-modal-body p-3 p-md-4">
                    <div class="row g-3">
                        {{-- Left Column: Employment Information --}}
                        <div class="col-md-6">
                            <div class="ss-section-box">
                                <div class="ss-box-title">
                                    <i class="bi bi-briefcase-fill me-2"></i>Employment Details
                                </div>
                                <div class="ss-kv-list">
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Employee Code</span>
                                        <span class="ss-kv-val font-monospace" id="modal-emp-code-detail">-</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Employment Type</span>
                                        <span class="ss-kv-val" id="modal-emp-type-detail">-</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Employment Status</span>
                                        <span class="ss-kv-val" id="modal-emp-status-detail">-</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Date of Joining</span>
                                        <span class="ss-kv-val" id="modal-emp-doj-detail">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Contact Details --}}
                        <div class="col-md-6">
                            <div class="ss-section-box">
                                <div class="ss-box-title">
                                    <i class="bi bi-person-lines-fill me-2"></i>Contact Information
                                </div>
                                <div class="ss-kv-list">
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Official Email</span>
                                        <a class="ss-kv-val ss-link-val text-truncate" id="modal-emp-email-link" href="#" target="_blank">
                                            <i class="bi bi-envelope me-1"></i><span id="modal-emp-email">-</span>
                                        </a>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Personal Email</span>
                                        <span class="ss-kv-val text-truncate" id="modal-emp-personal-email">-</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Primary Phone</span>
                                        <a class="ss-kv-val ss-link-val" id="modal-emp-phone-link" href="#">
                                            <i class="bi bi-telephone me-1"></i><span id="modal-emp-phone">-</span>
                                        </a>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Secondary Phone</span>
                                        <span class="ss-kv-val" id="modal-emp-phone-secondary">-</span>
                                    </div>
                                </div>
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


{{-- ================= STYLES ================= --}}
@push('styles')
    <style>
        #employeeModal { display: none; }
        #employeeModal.show { display: block; }
        .modal-backdrop { z-index: 1050; }
        #employeeModal { z-index: 1055; }

        /* ---- Toolbar Card ---- */
        .ss-toolbar-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            padding: 16px 18px;
            box-shadow: var(--card-shadow);
            transition: background 0.3s ease, border-color 0.3s ease;
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
            font-size: 14px;
            pointer-events: none;
        }
        .ss-search-input {
            width: 100%;
            height: 42px;
            padding: 8px 75px 8px 38px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 13px;
            outline: none;
            transition: all .2s ease;
        }
        .ss-search-input:focus {
            background: var(--bg-card);
            border-color: var(--theme-primary);
            box-shadow: 0 0 0 3px var(--theme-focus-ring);
        }
        .ss-search-input::placeholder {
            color: var(--text-muted);
            font-size: 12.5px;
        }

        .ss-search-spinner {
            position: absolute;
            right: 42px;
            color: var(--theme-primary);
        }
        .ss-clear-btn {
            position: absolute;
            right: 36px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            border-radius: 50%;
            transition: color .15s;
        }
        .ss-clear-btn:hover {
            color: var(--theme-primary);
        }
        .ss-kbd-hint {
            position: absolute;
            right: 12px;
            background: var(--bg-hover-row);
            border: 1px solid var(--border-subtle);
            color: var(--text-muted);
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            pointer-events: none;
        }

        /* Select controls */
        .ss-select {
            height: 42px;
            padding: 8px 36px 8px 14px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
            color-scheme: light;
        }
        .ss-select:hover {
            border-color: var(--border-hover);
            background-color: var(--bg-hover-row);
        }
        .ss-select:focus {
            border-color: var(--theme-primary) !important;
            box-shadow: 0 0 0 3px var(--theme-focus-ring) !important;
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
            outline: none !important;
        }
        .ss-select option {
            background-color: var(--bg-card);
            color: var(--text-primary);
            padding: 10px 14px;
            font-size: 13.5px;
            font-weight: 500;
        }
        .ss-select option:checked {
            background-color: var(--theme-primary-light);
            color: var(--theme-primary);
            font-weight: 700;
        }
        html.dark .ss-select option {
            background-color: #131d31;
            color: #f8fafc;
        }

        /* Reset button */
        .ss-reset-btn {
            height: 42px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s;
        }
        .ss-reset-btn:hover {
            background: var(--bg-hover-row);
            color: var(--theme-primary);
            border-color: var(--border-hover);
        }

        /* Toolbar meta bar */
        .ss-toolbar-meta {
            border-color: var(--border-subtle) !important;
            font-size: 12px;
        }
        .ss-result-badge {
            color: var(--text-secondary);
            font-size: 12.5px;
        }
        .ss-result-badge strong {
            color: var(--text-primary);
        }
        .ss-filter-pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            background: var(--theme-primary-light);
            color: var(--theme-primary);
            border: 1px solid var(--theme-focus-ring);
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        /* ---- Card ---- */
        .ss-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        /* ---- Table ---- */
        .ss-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; margin-bottom: 0; background: transparent; }
        .ss-table thead th { background: var(--bg-elevated); color: var(--text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .45px; padding: 15px 18px; border: none; border-bottom: 1px solid var(--border-subtle); white-space: nowrap; }
        .ss-table tbody td { padding: 16px 18px; vertical-align: middle; border: none; border-bottom: 1px solid var(--border-subtle); color: var(--text-secondary); background: transparent; }
        .ss-table tbody tr:last-child td { border-bottom: none; }
        .ss-table tbody tr:hover td { background: var(--bg-hover-row); }

        /* Badges & Elements */
        .ss-badge-reg { display: inline-block; padding: 5px 10px; border-radius: 6px; background: var(--bg-elevated); color: var(--text-secondary); border: 1px solid var(--border-subtle); font-weight: 600; font-size: 12px; }
        .ss-name { color: var(--text-primary); font-weight: 600; }
        .ss-avatar-mini {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--theme-gradient);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px var(--theme-glow);
        }
        .ss-sub-info { font-size: 11px; color: var(--text-muted); margin-top: 1px; }

        .ss-email-link, .ss-phone-link {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color .15s;
        }
        .ss-email-link:hover, .ss-phone-link:hover {
            color: var(--theme-primary);
        }

        .ss-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .ss-status-active {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }
        .ss-status-inactive {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.28);
        }

        .ss-dot-pulse {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 6px #10b981;
        }

        /* View button */
        .ss-view-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 14px; border-radius: 7px; background: var(--theme-gradient); color: #fff !important; border: none; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .2s; box-shadow: 0 4px 12px var(--theme-glow); }
        .ss-view-btn:hover { filter: brightness(1.1); transform: translateY(-1px); }
        .ss-view-btn svg { transition: transform .15s; }
        .ss-view-btn:hover svg { transform: translateX(2px); }

        /* Empty State */
        .ss-empty { text-align: center; padding: 48px 16px !important; color: var(--text-muted); }
        .ss-empty-icon { font-size: 32px; margin-bottom: 12px; opacity: .8; }
        .ss-empty strong { display: block; color: var(--text-primary); font-size: 15px; margin-bottom: 6px; }
        .ss-empty p { margin: 0; font-size: 13px; color: var(--text-muted); max-width: 420px; margin: 0 auto; }

        /* Pagination */
        .ss-pagination-wrap { display: flex; justify-content: center; align-items: center; padding: 18px 12px 22px; background: var(--bg-card); border-top: 1px solid var(--border-subtle); }
        .ss-pagination-wrap nav { width: 100%; text-align: center; }
        .ss-pagination-wrap .pagination { display: flex !important; flex-direction: row !important; flex-wrap: wrap; justify-content: center; align-items: center; margin-bottom: 0; gap: 4px; list-style: none; padding-left: 0; }
        .ss-pagination-wrap .page-item { display: inline-block; }
        .ss-pagination-wrap .page-link { display: inline-block; border-radius: 8px !important; border: 1px solid var(--border-subtle); color: var(--text-secondary) !important; background: var(--bg-card); padding: 7px 12px; font-size: 13px; font-weight: 500; margin: 0 2px; text-decoration: none; box-shadow: none; }
        .ss-pagination-wrap .page-link:hover { background: var(--theme-primary-light); border-color: var(--theme-focus-ring); color: var(--theme-primary) !important; }
        .ss-pagination-wrap .page-item.active .page-link { background: var(--theme-gradient) !important; border-color: var(--theme-primary) !important; color: #fff !important; font-weight: 600; box-shadow: 0 2px 8px var(--theme-glow); }
        .ss-pagination-wrap .page-item.disabled .page-link { color: var(--text-muted) !important; background: var(--bg-elevated); border-color: var(--border-subtle); }
        .ss-pagination-wrap svg { width: 14px; height: 14px; vertical-align: middle; }

        /* Modal Styles */
        .ss-modal-content {
            border: 1px solid var(--border-card);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.28);
            background: var(--bg-card);
        }
        .ss-modal-hero {
            background: var(--theme-gradient);
            color: #fff;
            padding: 22px 24px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }
        .ss-hero-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
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

        .ss-copy-btn {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.85);
            cursor: pointer;
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 12px;
            transition: all .15s;
        }
        .ss-copy-btn:hover { color: #fff; background: rgba(255, 255, 255, 0.25); }

        .ss-modal-body { background: var(--bg-main); max-height: 70vh; overflow-y: auto; }
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
        .ss-link-val { color: var(--theme-primary) !important; text-decoration: none; display: inline-flex; align-items: center; }
        .ss-link-val:hover { opacity: .85; text-decoration: underline; }

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
            transition: background .2s;
        }
        .ss-close-btn:hover { background: var(--bg-hover-row); color: var(--text-primary); }
    </style>
@endpush


{{-- ================= SCRIPTS ================= --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ================= MODAL DATA POPULATE =================
            var modalEl = document.getElementById('employeeModal');
            if (modalEl) {
                if (modalEl.parentNode !== document.body) {
                    document.body.appendChild(modalEl);
                }

                modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (window.bootstrap && bootstrap.Modal) {
                            var inst = bootstrap.Modal.getOrCreateInstance(modalEl);
                            inst.hide();
                        }
                    });
                });

                modalEl.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;

                    var get = function (attr) {
                        return button.getAttribute('data-' + attr) || '-';
                    };

                    var code = get('code');
                    var name = get('name');
                    var email = get('email');
                    var personalEmail = get('personal-email');
                    var phone = get('phone');
                    var phoneSecondary = get('phone-secondary');
                    var type = get('type');
                    var status = get('status');
                    var doj = get('doj');

                    // Header & Ribbon
                    var avatarEl = document.getElementById('modal-emp-avatar');
                    if (avatarEl) avatarEl.textContent = name !== '-' ? name.charAt(0).toUpperCase() : 'E';

                    var nameEl = document.getElementById('modal-emp-name');
                    if (nameEl) nameEl.textContent = name;

                    var typeBadgeEl = document.getElementById('modal-emp-type');
                    if (typeBadgeEl) typeBadgeEl.textContent = type !== '-' ? type : 'STAFF';

                    var codeSubEl = document.getElementById('modal-emp-code-sub');
                    if (codeSubEl) codeSubEl.textContent = 'CODE: ' + code;

                    var statusEl = document.getElementById('modal-emp-status');
                    if (statusEl) statusEl.textContent = status;

                    var codeRibbon = document.getElementById('modal-emp-code');
                    if (codeRibbon) codeRibbon.textContent = code;

                    var typeRibbon = document.getElementById('modal-emp-type-ribbon');
                    if (typeRibbon) typeRibbon.textContent = type;

                    var statusRibbon = document.getElementById('modal-emp-status-ribbon');
                    if (statusRibbon) statusRibbon.textContent = status;

                    var dojRibbon = document.getElementById('modal-emp-doj');
                    if (dojRibbon) dojRibbon.textContent = doj;

                    // Employment Details
                    var codeDetail = document.getElementById('modal-emp-code-detail');
                    if (codeDetail) codeDetail.textContent = code;

                    var typeDetail = document.getElementById('modal-emp-type-detail');
                    if (typeDetail) typeDetail.textContent = type;

                    var statusDetail = document.getElementById('modal-emp-status-detail');
                    if (statusDetail) statusDetail.textContent = status;

                    var dojDetail = document.getElementById('modal-emp-doj-detail');
                    if (dojDetail) dojDetail.textContent = doj;

                    // Contact Details
                    var emailEl = document.getElementById('modal-emp-email');
                    var emailLink = document.getElementById('modal-emp-email-link');
                    if (emailEl) emailEl.textContent = email;
                    if (emailLink) emailLink.href = (email && email !== '-') ? ('mailto:' + email) : '#';

                    var personalEmailEl = document.getElementById('modal-emp-personal-email');
                    if (personalEmailEl) personalEmailEl.textContent = personalEmail;

                    var phoneEl = document.getElementById('modal-emp-phone');
                    var phoneLink = document.getElementById('modal-emp-phone-link');
                    if (phoneEl) phoneEl.textContent = phone;
                    if (phoneLink) phoneLink.href = (phone && phone !== '-') ? ('tel:' + phone) : '#';

                    var phoneSecEl = document.getElementById('modal-emp-phone-secondary');
                    if (phoneSecEl) phoneSecEl.textContent = phoneSecondary;
                });
            }

            // Copy Emp Code
            var copyBtn = document.getElementById('copyEmpCodeBtn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    var codeEl = document.getElementById('modal-emp-code');
                    if (!codeEl || !codeEl.textContent || codeEl.textContent === '-') return;

                    navigator.clipboard.writeText(codeEl.textContent.trim()).then(function () {
                        var icon = document.getElementById('copyEmpCodeIcon');
                        if (icon) {
                            icon.className = 'bi bi-check2';
                            setTimeout(function () {
                                icon.className = 'bi bi-copy';
                            }, 1500);
                        }
                    });
                });
            }

            // ================= LIVE SEARCH ENGINE =================
            const searchInput = document.getElementById('liveSearchInput');
            const clearBtn = document.getElementById('clearSearchBtn');
            const spinner = document.getElementById('searchSpinner');
            const filterType = document.getElementById('filterType');
            const filterStatus = document.getElementById('filterStatus');
            const resetBtn = document.getElementById('resetFiltersBtn');
            const tableBody = document.getElementById('employeeTableBody');
            const paginationWrap = document.getElementById('paginationWrap');
            const countFrom = document.getElementById('countFrom');
            const countTo = document.getElementById('countTo');
            const countTotal = document.getElementById('countTotal');
            const activeFiltersBadge = document.getElementById('activeFiltersBadge');

            let debounceTimer = null;
            let currentAbortController = null;

            function updateFilterBadge() {
                const isFiltered = (searchInput.value.trim() !== '') ||
                    (filterType.value !== 'all') ||
                    (filterStatus.value !== 'all');

                if (activeFiltersBadge) {
                    activeFiltersBadge.style.display = isFiltered ? 'inline-flex' : 'none';
                }
            }

            function performSearch(page = 1) {
                const q = searchInput.value.trim();
                const type = filterType.value;
                const status = filterStatus.value;

                clearBtn.style.display = q ? 'flex' : 'none';
                updateFilterBadge();

                if (currentAbortController) {
                    currentAbortController.abort();
                }
                currentAbortController = new AbortController();

                if (spinner) spinner.style.display = 'inline-block';

                const params = new URLSearchParams();
                if (q) params.set('q', q);
                if (type && type !== 'all') params.set('type', type);
                if (status && status !== 'all') params.set('status', status);
                if (page > 1) params.set('page', page);

                const url = '{{ route("hrms.empsearch") }}?' + params.toString();

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: currentAbortController.signal
                })
                .then(response => {
                    if (!response.ok) throw new Error('Search failed');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        tableBody.innerHTML = data.html;
                        if (paginationWrap) {
                            paginationWrap.innerHTML = data.pagination || '';
                            paginationWrap.style.display = (data.pagination && data.count > 0) ? 'flex' : 'none';
                        }
                        if (countFrom) countFrom.textContent = data.from;
                        if (countTo) countTo.textContent = data.to;
                        if (countTotal) countTotal.textContent = data.total;

                        window.history.replaceState({}, '', url);
                    }
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        console.error('Search error:', err);
                    }
                })
                .finally(() => {
                    if (spinner) spinner.style.display = 'none';
                });
            }

            // Input listener with debounce
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    performSearch(1);
                }, 250);
            });

            // Filters
            [filterType, filterStatus].forEach(function (select) {
                if (select) {
                    select.addEventListener('change', function () {
                        performSearch(1);
                    });
                }
            });

            // Clear
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.focus();
                performSearch(1);
            });

            // Reset
            resetBtn.addEventListener('click', function () {
                searchInput.value = '';
                filterType.value = 'all';
                filterStatus.value = 'all';
                performSearch(1);
            });

            // Pagination
            paginationWrap.addEventListener('click', function (e) {
                const link = e.target.closest('a.page-link');
                if (link && link.href) {
                    e.preventDefault();
                    try {
                        const urlObj = new URL(link.href);
                        const page = urlObj.searchParams.get('page') || 1;
                        performSearch(page);
                    } catch (err) {
                        window.location.href = link.href;
                    }
                }
            });

            // Shortcuts
            document.addEventListener('keydown', function (e) {
                if (e.key === '/' && document.activeElement !== searchInput && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                } else if (e.key === 'Escape' && document.activeElement === searchInput) {
                    if (searchInput.value !== '') {
                        searchInput.value = '';
                        performSearch(1);
                    } else {
                        searchInput.blur();
                    }
                }
            });

            updateFilterBadge();
        });
    </script>
@endpush
