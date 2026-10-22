@extends('layouts.app')

@section('page_title', 'Student Search')

@section('content')

    <div class="container-fluid px-0">

        {{-- ================= SEARCH & FILTER TOOLBAR ================= --}}
        <div class="ss-toolbar-card mb-3">
            <div class="row g-2 align-items-center">
                {{-- Live Search Input --}}
                <div class="col-lg-5 col-md-12">
                    <div class="ss-search-input-wrap">
                        <span class="ss-search-icon">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text"
                               id="liveSearchInput"
                               class="ss-search-input"
                               placeholder="Search by reg no, name, batch, program, email... (Press / to search)"
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

                {{-- Filter: Program --}}
                <div class="col-lg-2 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterProgram" class="form-select ss-select">
                            <option value="all">All Programs</option>
                            @foreach ($programs as $p)
                                <option value="{{ $p->program_id }}" {{ (string)($program ?? '') === (string)$p->program_id ? 'selected' : '' }}>
                                    {{ $p->program_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter: Batch --}}
                <div class="col-lg-2 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterBatch" class="form-select ss-select">
                            <option value="all">All Batches</option>
                            @foreach ($batches as $b)
                                <option value="{{ $b }}" {{ ($batch ?? '') === $b ? 'selected' : '' }}>
                                    {{ $b }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter: Regulation --}}
                <div class="col-lg-1 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterRegulation" class="form-select ss-select">
                            <option value="all">All Reg</option>
                            @foreach ($regulations as $r)
                                <option value="{{ $r }}" {{ ($regulation ?? '') === $r ? 'selected' : '' }}>
                                    {{ $r }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter: Status --}}
                <div class="col-lg-1 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterStatus" class="form-select ss-select">
                            <option value="all">Status</option>
                            @foreach ($statuses as $st)
                                <option value="{{ $st }}" {{ ($status ?? '') === $st ? 'selected' : '' }}>
                                    {{ $st }}
                                </option>
                            @endforeach
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
                        Showing <strong id="countFrom">{{ $students->firstItem() ?? 0 }}</strong> -
                        <strong id="countTo">{{ $students->lastItem() ?? 0 }}</strong> of
                        <strong id="countTotal">{{ $students->total() }}</strong> students
                    </span>
                    <span id="activeFiltersBadge" class="ss-filter-pill" style="display: none;">
                        Filtered
                    </span>
                </div>
                <div class="ss-meta-right text-muted small">
                    <span class="me-2"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Live Semantic Search</span>
                </div>
            </div>
        </div>

        {{-- ================= TABLE CARD ================= --}}
        <div class="ss-card">

            <div class="table-responsive">
                <table class="table ss-table align-middle mb-0">
                    <thead>
                    <tr>
                        <th style="width: 20%;">Reg. No</th>
                        <th style="width: 35%;">Name</th>
                        <th style="width: 15%;">Batch</th>
                        <th style="width: 15%;">Regulation</th>
                        <th style="width: 15%; text-align: center;">Action</th>
                    </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        @include('admissions::partials.student-rows', ['students' => $students])
                    </tbody>
                </table>
            </div>

            {{-- ================= PAGINATION ================= --}}
            <div class="ss-pagination-wrap" id="paginationWrap" style="{{ ($students->isEmpty() || !$students->hasPages()) ? 'display: none;' : '' }}">
                @if (method_exists($students, 'withQueryString') && $students->hasPages() && $students->count() > 0)
                    {{ $students->withQueryString()->links('pagination::bootstrap-5') }}
                @endif
            </div>

        </div>
    </div>

    {{-- ================= POPUP MODAL: MODERN STUDENT PROFILE ================= --}}
    <div class="modal fade" id="studentModal" tabindex="-1"
         aria-labelledby="studentModalLabel" aria-hidden="true"
         data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content ss-modal-content">

                {{-- Hero Header --}}
                <div class="ss-modal-hero">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ss-hero-avatar" id="modal-avatar">A</div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h4 class="ss-hero-name mb-0" id="modal-name">-</h4>
                                    <span class="ss-hero-badge" id="modal-branch">-</span>
                                    <span class="ss-hero-status" id="modal-status-badge">
                                        <span class="ss-dot-pulse"></span>
                                        <span id="modal-status">-</span>
                                    </span>
                                </div>
                                <div class="ss-hero-sub" id="modal-program">-</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    {{-- Hero Quick Metric Ribbon --}}
                    <div class="ss-hero-ribbon mt-3">
                        <div class="ss-ribbon-item">
                            <span class="ss-ribbon-label">REGISTER NO</span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="ss-ribbon-val font-monospace" id="modal-regno">-</span>
                                <button type="button" class="ss-copy-btn" id="copyRegnoBtn" title="Copy Register No">
                                    <i class="bi bi-copy" id="copyRegnoIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="ss-ribbon-item">
                            <span class="ss-ribbon-label">BATCH</span>
                            <span class="ss-ribbon-val" id="modal-batch">-</span>
                        </div>
                        <div class="ss-ribbon-item">
                            <span class="ss-ribbon-label">REGULATION</span>
                            <span class="ss-ribbon-val" id="modal-regulation">-</span>
                        </div>
                        <div class="ss-ribbon-item">
                            <span class="ss-ribbon-label">ADMISSION DATE</span>
                            <span class="ss-ribbon-val" id="modal-admission">-</span>
                        </div>
                    </div>
                </div>

                {{-- Modal Body: 2 Clean Columns --}}
                <div class="modal-body ss-modal-body p-3 p-md-4">
                    <div class="row g-3">
                        {{-- Left Column: Academic & Enrollment --}}
                        <div class="col-md-6">
                            <div class="ss-section-box">
                                <div class="ss-box-title">
                                    <i class="bi bi-mortarboard-fill me-2"></i>Academic &amp; Enrolment
                                </div>
                                <div class="ss-kv-list">
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Program</span>
                                        <span class="ss-kv-val" id="modal-program-detail">—</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Application No</span>
                                        <span class="ss-kv-val font-monospace" id="modal-appno">—</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Student Type</span>
                                        <span class="ss-kv-val" id="modal-std-type">—</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Enrollment No</span>
                                        <span class="ss-kv-val font-monospace" id="modal-enrollment">—</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">ABC ID</span>
                                        <span class="ss-kv-val font-monospace" id="modal-abc">—</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">DEB ID</span>
                                        <span class="ss-kv-val font-monospace" id="modal-deb">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Personal & Contact --}}
                        <div class="col-md-6">
                            <div class="ss-section-box">
                                <div class="ss-box-title">
                                    <i class="bi bi-person-lines-fill me-2"></i>Personal &amp; Contact
                                </div>
                                <div class="ss-kv-list">
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Gender</span>
                                        <span class="ss-kv-val" id="modal-gender">—</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Date of Birth</span>
                                        <span class="ss-kv-val" id="modal-dob">—</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Aadhar Number</span>
                                        <span class="ss-kv-val font-monospace" id="modal-aadhar">—</span>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Personal Email</span>
                                        <a class="ss-kv-val ss-link-val text-truncate" id="modal-email-link" href="#" target="_blank">
                                            <i class="bi bi-envelope me-1"></i><span id="modal-email">—</span>
                                        </a>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Official Email</span>
                                        <a class="ss-kv-val ss-link-val text-truncate" id="modal-ofc-mail-link" href="#" target="_blank">
                                            <i class="bi bi-briefcase me-1"></i><span id="modal-ofc-mail">—</span>
                                        </a>
                                    </div>
                                    <div class="ss-kv-row">
                                        <span class="ss-kv-key">Mobile</span>
                                        <a class="ss-kv-val ss-link-val" id="modal-mobile-link" href="#">
                                            <i class="bi bi-telephone me-1"></i><span id="modal-mobile">—</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer with Action Link --}}
                <div class="modal-footer ss-modal-footer d-flex justify-content-between align-items-center">
                    <div>
                        <a href="#" id="modal-dossier-btn" target="_blank" class="ss-dossier-btn">
                            <i class="bi bi-file-earmark-person me-1"></i>
                            <span>Open Full Admission Form</span>
                            <i class="bi bi-arrow-up-right small ms-1"></i>
                        </a>
                    </div>
                    <button type="button" class="ss-close-btn" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

@endsection


{{-- ================= STYLES ================= --}}
@push('styles')
    <style>
        /* Ensure studentModal and backdrop behave properly with pointer events */
        #studentModal {
            z-index: 1065 !important;
            pointer-events: auto !important;
        }
        #studentModal .modal-dialog {
            z-index: 1066 !important;
            pointer-events: auto !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
            background-color: rgba(15, 23, 42, 0.45) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        .modal-backdrop.show {
            opacity: 0.65 !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

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

        /* ---- Badges & Details in Table ---- */
        .ss-badge-reg { display: inline-block; padding: 5px 10px; border-radius: 6px; background: var(--bg-elevated); color: var(--text-secondary); border: 1px solid var(--border-subtle); font-weight: 600; font-size: 12px; }
        .ss-badge-batch { display: inline-block; padding: 5px 10px; border-radius: 5px; background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25); font-size: 11px; font-weight: 700; }
        html.dark .ss-badge-batch { background: rgba(245, 158, 11, 0.18); color: #fbbf24; border-color: rgba(245, 158, 11, 0.35); }
        .ss-badge-regu { display: inline-block; padding: 5px 10px; border-radius: 30px; background: var(--bg-elevated); color: var(--text-primary); font-size: 12px; font-weight: 700; border: 1px solid var(--border-subtle); }
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
        .ss-sub-info {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        /* ---- View button ---- */
        .ss-view-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 14px; border-radius: 7px; background: var(--theme-gradient); color: #fff !important; border: none; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .2s; box-shadow: 0 4px 12px var(--theme-glow); }
        .ss-view-btn:hover { filter: brightness(1.1); transform: translateY(-1px); }
        .ss-view-btn svg { transition: transform .15s; }
        .ss-view-btn:hover svg { transform: translateX(2px); }

        /* ---- Empty State ---- */
        .ss-empty { text-align: center; padding: 48px 16px !important; color: var(--text-muted); }
        .ss-empty-icon { font-size: 32px; margin-bottom: 12px; opacity: .8; }
        .ss-empty strong { display: block; color: var(--text-primary); font-size: 15px; margin-bottom: 6px; }
        .ss-empty p { margin: 0; font-size: 13px; color: var(--text-muted); max-width: 420px; margin: 0 auto; }

        /* ---- Pagination ---- */
        .ss-pagination-wrap { display: flex; justify-content: center; align-items: center; padding: 18px 12px 22px; background: var(--bg-card); border-top: 1px solid var(--border-subtle); }
        .ss-pagination-wrap nav { width: 100%; text-align: center; }
        .ss-pagination-wrap .pagination { display: flex !important; flex-direction: row !important; flex-wrap: wrap; justify-content: center; align-items: center; margin-bottom: 0; gap: 4px; list-style: none; padding-left: 0; }
        .ss-pagination-wrap .page-item { display: inline-block; }
        .ss-pagination-wrap .page-link { display: inline-block; border-radius: 8px !important; border: 1px solid var(--border-subtle); color: var(--text-secondary) !important; background: var(--bg-card); padding: 7px 12px; font-size: 13px; font-weight: 500; margin: 0 2px; text-decoration: none; box-shadow: none; }
        .ss-pagination-wrap .page-link:hover { background: var(--theme-primary-light); border-color: var(--theme-focus-ring); color: var(--theme-primary) !important; }
        .ss-pagination-wrap .page-item.active .page-link { background: var(--theme-gradient) !important; border-color: var(--theme-primary) !important; color: #fff !important; font-weight: 600; box-shadow: 0 2px 8px var(--theme-glow); }
        .ss-pagination-wrap .page-item.disabled .page-link { color: var(--text-muted) !important; background: var(--bg-elevated); border-color: var(--border-subtle); }
        .ss-pagination-wrap svg { width: 14px; height: 14px; vertical-align: middle; }

        /* ---- Modern Student Profile Modal ---- */
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

        .ss-dot-pulse {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 6px #10b981;
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
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .ss-ribbon-item {
            display: flex;
            flex-direction: column;
        }

        .ss-ribbon-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.6px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 2px;
        }

        .ss-ribbon-val {
            font-size: 13.5px;
            font-weight: 600;
            color: #fff;
        }

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
        .ss-copy-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.25);
        }

        .ss-modal-body {
            background: var(--bg-main);
            max-height: 70vh;
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

        .ss-kv-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .ss-kv-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 4px;
            font-size: 12.5px;
            border-bottom: 1px dashed var(--border-subtle);
            transition: background 0.15s;
        }
        .ss-kv-row:last-child {
            border-bottom: none;
        }
        .ss-kv-row:hover {
            background: var(--bg-hover-row);
            border-radius: 4px;
        }

        .ss-kv-key {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .ss-kv-val {
            color: var(--text-primary);
            font-weight: 600;
            text-align: right;
            max-width: 62%;
            word-break: break-word;
        }

        .ss-link-val {
            color: var(--theme-primary) !important;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: opacity .15s;
        }
        .ss-link-val:hover {
            opacity: .85;
            text-decoration: underline;
        }

        .ss-modal-footer {
            background: var(--bg-card);
            border-top: 1px solid var(--border-subtle);
            padding: 14px 22px;
        }

        .ss-dossier-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 9px;
            background: var(--theme-gradient);
            color: #fff !important;
            border: none;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 14px var(--theme-glow);
            transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .ss-dossier-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.08);
            box-shadow: 0 6px 18px var(--theme-glow);
            color: #fff !important;
        }

        .ss-close-btn {
            background: var(--bg-elevated);
            color: var(--text-secondary);
            border: 1px solid var(--border-subtle);
            border-radius: 9px;
            padding: 8px 22px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
        }
        .ss-close-btn:hover {
            background: var(--bg-hover-row);
            color: var(--text-primary);
            border-color: var(--border-hover);
        }
    </style>
@endpush


{{-- ================= SCRIPTS ================= --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ================= MODAL DATA POPULATE =================
            var modalEl = document.getElementById('studentModal');
            if (modalEl) {
                // Relocate modal directly to <body> so stacking context never traps it
                if (modalEl.parentNode !== document.body) {
                    document.body.appendChild(modalEl);
                }

                // Explicit dismiss handlers for close buttons
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
                        var val = button.getAttribute('data-' + attr);
                        return (val && val !== '-' && val !== 'null' && val.trim() !== '') ? val.trim() : '—';
                    };

                    var name = get('name');
                    var regno = get('regno');
                    var program = get('program');
                    var programCode = get('program-code');
                    var batch = get('batch');
                    var regulation = get('regulation');
                    var status = get('status');
                    var admission = get('admission');
                    var appno = get('appno');
                    var ofcMail = get('ofc-mail');
                    var gender = get('gender');
                    var dob = get('dob');
                    var email = get('email');
                    var mobile = get('mobile');
                    var stdType = get('std-type');
                    var enrollment = get('enrollment');
                    var aadhar = get('aadhar');
                    var abc = get('abc');
                    var deb = get('deb');

                    // Header & Ribbon
                    var avatarEl = document.getElementById('modal-avatar');
                    if (avatarEl) avatarEl.textContent = name !== '—' ? name.charAt(0).toUpperCase() : 'S';

                    var nameEl = document.getElementById('modal-name');
                    if (nameEl) nameEl.textContent = name;

                    var branchEl = document.getElementById('modal-branch');
                    if (branchEl) branchEl.textContent = programCode !== '—' ? programCode : 'PROGRAM';

                    var progEl = document.getElementById('modal-program');
                    if (progEl) progEl.textContent = program !== '—' ? program : '';

                    var statusEl = document.getElementById('modal-status');
                    if (statusEl) statusEl.textContent = status;

                    var regnoEl = document.getElementById('modal-regno');
                    if (regnoEl) regnoEl.textContent = regno;

                    var batchEl = document.getElementById('modal-batch');
                    if (batchEl) batchEl.textContent = batch;

                    var reguEl = document.getElementById('modal-regulation');
                    if (reguEl) reguEl.textContent = regulation;

                    var admEl = document.getElementById('modal-admission');
                    if (admEl) admEl.textContent = admission;

                    // Academic Details
                    var progDetailEl = document.getElementById('modal-program-detail');
                    if (progDetailEl) progDetailEl.textContent = program;

                    var appnoEl = document.getElementById('modal-appno');
                    if (appnoEl) appnoEl.textContent = appno;

                    var stdTypeEl = document.getElementById('modal-std-type');
                    if (stdTypeEl) stdTypeEl.textContent = stdType;

                    var enrollEl = document.getElementById('modal-enrollment');
                    if (enrollEl) enrollEl.textContent = enrollment;

                    var abcEl = document.getElementById('modal-abc');
                    if (abcEl) abcEl.textContent = abc;

                    var debEl = document.getElementById('modal-deb');
                    if (debEl) debEl.textContent = deb;

                    // Personal & Contact Details
                    var genderEl = document.getElementById('modal-gender');
                    if (genderEl) genderEl.textContent = gender;

                    var dobEl = document.getElementById('modal-dob');
                    if (dobEl) dobEl.textContent = dob;

                    var aadharEl = document.getElementById('modal-aadhar');
                    if (aadharEl) aadharEl.textContent = aadhar;

                    var emailEl = document.getElementById('modal-email');
                    var emailLink = document.getElementById('modal-email-link');
                    if (emailEl) emailEl.textContent = email;
                    if (emailLink) emailLink.href = (email && email !== '—') ? ('mailto:' + email) : '#';

                    var ofcMailEl = document.getElementById('modal-ofc-mail');
                    var ofcMailLink = document.getElementById('modal-ofc-mail-link');
                    if (ofcMailEl) ofcMailEl.textContent = ofcMail;
                    if (ofcMailLink) ofcMailLink.href = (ofcMail && ofcMail !== '—') ? ('mailto:' + ofcMail) : '#';

                    var mobileEl = document.getElementById('modal-mobile');
                    var mobileLink = document.getElementById('modal-mobile-link');
                    if (mobileEl) mobileEl.textContent = mobile;
                    if (mobileLink) mobileLink.href = (mobile && mobile !== '—') ? ('tel:' + mobile) : '#';

                    // Full dossier link
                    var dossierBtn = document.getElementById('modal-dossier-btn');
                    if (dossierBtn) {
                        dossierBtn.href = regno && regno !== '—' ? ('{{ url("admissions/info") }}/' + encodeURIComponent(regno)) : '#';
                    }
                });
            }

            // Copy Reg No functionality
            var copyBtn = document.getElementById('copyRegnoBtn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    var regnoEl = document.getElementById('modal-regno');
                    if (!regnoEl || !regnoEl.textContent || regnoEl.textContent === '—') return;

                    navigator.clipboard.writeText(regnoEl.textContent.trim()).then(function () {
                        var icon = document.getElementById('copyRegnoIcon');
                        if (icon) {
                            icon.className = 'bi bi-check2';
                            setTimeout(function () {
                                icon.className = 'bi bi-copy';
                            }, 1500);
                        }
                    });
                });
            }

            // ================= LIVE & SEMANTIC SEARCH ENGINE =================
            const searchInput = document.getElementById('liveSearchInput');
            const clearBtn = document.getElementById('clearSearchBtn');
            const spinner = document.getElementById('searchSpinner');
            const filterProgram = document.getElementById('filterProgram');
            const filterBatch = document.getElementById('filterBatch');
            const filterRegulation = document.getElementById('filterRegulation');
            const filterStatus = document.getElementById('filterStatus');
            const resetBtn = document.getElementById('resetFiltersBtn');
            const tableBody = document.getElementById('studentTableBody');
            const paginationWrap = document.getElementById('paginationWrap');
            const countFrom = document.getElementById('countFrom');
            const countTo = document.getElementById('countTo');
            const countTotal = document.getElementById('countTotal');
            const activeFiltersBadge = document.getElementById('activeFiltersBadge');

            let debounceTimer = null;
            let currentAbortController = null;

            function updateFilterBadge() {
                const isFiltered = (searchInput.value.trim() !== '') ||
                    (filterProgram.value !== 'all') ||
                    (filterBatch.value !== 'all') ||
                    (filterRegulation.value !== 'all') ||
                    (filterStatus.value !== 'all');

                if (activeFiltersBadge) {
                    activeFiltersBadge.style.display = isFiltered ? 'inline-flex' : 'none';
                }
            }

            function performSearch(page = 1) {
                const q = searchInput.value.trim();
                const program = filterProgram.value;
                const batch = filterBatch.value;
                const regulation = filterRegulation.value;
                const status = filterStatus.value;

                clearBtn.style.display = q ? 'flex' : 'none';
                updateFilterBadge();

                // Abort any pending fetch to prevent race conditions
                if (currentAbortController) {
                    currentAbortController.abort();
                }
                currentAbortController = new AbortController();

                // Show spinner
                if (spinner) spinner.style.display = 'inline-block';

                const params = new URLSearchParams();
                if (q) params.set('q', q);
                if (program && program !== 'all') params.set('program', program);
                if (batch && batch !== 'all') params.set('batch', batch);
                if (regulation && regulation !== 'all') params.set('regulation', regulation);
                if (status && status !== 'all') params.set('status', status);
                if (page > 1) params.set('page', page);

                const url = '{{ route("admissions.stdsearch") }}?' + params.toString();

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

                        // Sync URL in address bar without reloading
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

            // Input listener with 250ms debounce
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    performSearch(1);
                }, 250);
            });

            // Filter dropdown change listeners
            [filterProgram, filterBatch, filterRegulation, filterStatus].forEach(function (select) {
                if (select) {
                    select.addEventListener('change', function () {
                        performSearch(1);
                    });
                }
            });

            // Clear search button
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchInput.focus();
                performSearch(1);
            });

            // Reset all filters button
            resetBtn.addEventListener('click', function () {
                searchInput.value = '';
                filterProgram.value = 'all';
                filterBatch.value = 'all';
                filterRegulation.value = 'all';
                filterStatus.value = 'all';
                performSearch(1);
            });

            // Intercept pagination clicks for smooth AJAX pagination
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

            // Keyboard shortcut: '/' or 'Ctrl+K' focuses search input, 'Escape' clears it
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

            // Initial badge state
            updateFilterBadge();
        });
    </script>
@endpush

