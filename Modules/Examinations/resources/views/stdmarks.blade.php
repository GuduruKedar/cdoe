@extends('layouts.app')

@section('page_title', 'Student Marks')

@section('content')

    <div class="container-fluid px-0">

        {{-- ================= SEARCH & FILTER TOOLBAR ================= --}}
        <div class="ss-toolbar-card mb-3">
            <div class="row g-2 align-items-center">
                {{-- Live Search Input --}}
                <div class="col-xl-3 col-lg-3 col-md-12">
                    <div class="ss-search-input-wrap">
                        <span class="ss-search-icon">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text"
                               id="liveSearchInput"
                               class="ss-search-input"
                               placeholder="Search by reg no, student, memo, division, batch... (Press / to search)"
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

                {{-- Filter: Batch --}}
                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterBatch" class="form-select ss-select">
                            <option value="all">All Batches</option>
                            @foreach ($batches as $b)
                                <option value="{{ $b }}" {{ (string)($batch ?? '') === (string)$b ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', $b) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter: Semester --}}
                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterSem" class="form-select ss-select">
                            <option value="all">All Semesters</option>
                            @foreach ($semesters as $s)
                                <option value="{{ $s }}" {{ (string)($sem ?? '') === (string)$s ? 'selected' : '' }}>
                                    Semester {{ $s }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter: Exam Type --}}
                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterType" class="form-select ss-select">
                            <option value="all">All Exam Types</option>
                            @foreach ($types as $t)
                                <option value="{{ $t }}" {{ ($type ?? '') === $t ? 'selected' : '' }}>
                                    {{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter: Division --}}
                <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6">
                    <div class="ss-select-wrap">
                        <select id="filterDivision" class="form-select ss-select">
                            <option value="all">All Divisions</option>
                            @foreach ($divisions as $d)
                                <option value="{{ $d }}" {{ ($division ?? '') === $d ? 'selected' : '' }}>
                                    {{ $d }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Reset Button --}}
                <div class="col-xl-1 col-lg-1 col-12 text-end">
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
                        Showing <strong id="countFrom">{{ $marksMemoList->firstItem() ?? 0 }}</strong> -
                        <strong id="countTo">{{ $marksMemoList->lastItem() ?? 0 }}</strong> of
                        <strong id="countTotal">{{ $marksMemoList->total() }}</strong> records
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
                        <th style="width: 12%;">Reg. No</th>
                        <th style="width: 20%;">Student Name</th>
                        <th style="width: 13%;">Batch</th>
                        <th style="width: 10%;">Year &amp; Sem</th>
                        <th style="width: 9%;">Type</th>
                        <th style="width: 13%;">Division</th>
                        <th style="width: 11%;">Exam Session</th>
                        <th style="width: 7%;">SGPA / CGPA</th>
                        <th style="width: 5%; text-align: center;">Action</th>
                    </tr>
                    </thead>
                    <tbody id="marksTableBody">
                        @include('examinations::partials.marks-rows', ['marksMemoList' => $marksMemoList])
                    </tbody>
                </table>
            </div>

            {{-- ================= PAGINATION ================= --}}
            <div class="ss-pagination-wrap" id="paginationWrap" style="{{ ($marksMemoList->isEmpty() || !$marksMemoList->hasPages()) ? 'display: none;' : '' }}">
                @if (method_exists($marksMemoList, 'withQueryString') && $marksMemoList->hasPages() && $marksMemoList->count() > 0)
                    {{ $marksMemoList->withQueryString()->links('pagination::bootstrap-5') }}
                @endif
            </div>

        </div>
    </div>

@endsection


{{-- ================= STYLES ================= --}}
@push('styles')
    <style>
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
        .ss-badge-batch { display: inline-block; padding: 4px 8px; border-radius: 5px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.25); font-size: 11px; font-weight: 700; }
        .ss-badge-regu { display: inline-block; padding: 4px 8px; border-radius: 20px; background: var(--bg-elevated); color: var(--text-primary); font-size: 11.5px; font-weight: 700; border: 1px solid var(--border-subtle); }
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
        .ss-sub-info { font-size: 11px; margin-top: 1px; }

        /* Division pills */
        .ss-div-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .div-first {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }
        .div-second {
            background: rgba(59, 130, 246, 0.12);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.28);
        }
        .div-fail {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.28);
        }
        .div-other {
            background: var(--bg-elevated);
            color: var(--text-secondary);
            border: 1px solid var(--border-subtle);
        }

        .ss-badge-batch-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: var(--bg-hover-row);
            border: 1px solid var(--border-subtle);
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        html.dark .ss-badge-batch-tag {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
        }

        /* View button */
        .ss-view-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 14px; border-radius: 7px; background: var(--theme-gradient); color: #fff !important; border: none; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .2s; box-shadow: 0 4px 12px var(--theme-glow); }
        .ss-view-btn:hover { filter: brightness(1.1); transform: translateY(-1px); color: #fff !important; }
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
    </style>
@endpush


{{-- ================= SCRIPTS ================= --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('liveSearchInput');
            const clearBtn = document.getElementById('clearSearchBtn');
            const spinner = document.getElementById('searchSpinner');
            const filterBatch = document.getElementById('filterBatch');
            const filterSem = document.getElementById('filterSem');
            const filterType = document.getElementById('filterType');
            const filterDivision = document.getElementById('filterDivision');
            const resetBtn = document.getElementById('resetFiltersBtn');
            const tableBody = document.getElementById('marksTableBody');
            const paginationWrap = document.getElementById('paginationWrap');
            const countFrom = document.getElementById('countFrom');
            const countTo = document.getElementById('countTo');
            const countTotal = document.getElementById('countTotal');
            const activeFiltersBadge = document.getElementById('activeFiltersBadge');

            let debounceTimer = null;
            let currentAbortController = null;

            function updateFilterBadge() {
                const isFiltered = (searchInput.value.trim() !== '') ||
                    (filterBatch && filterBatch.value !== 'all') ||
                    (filterSem.value !== 'all') ||
                    (filterType.value !== 'all') ||
                    (filterDivision.value !== 'all');

                if (activeFiltersBadge) {
                    activeFiltersBadge.style.display = isFiltered ? 'inline-flex' : 'none';
                }
            }

            function performSearch(page = 1) {
                const q = searchInput.value.trim();
                const batch = filterBatch ? filterBatch.value : 'all';
                const sem = filterSem.value;
                const type = filterType.value;
                const division = filterDivision.value;

                clearBtn.style.display = q ? 'flex' : 'none';
                updateFilterBadge();

                if (currentAbortController) {
                    currentAbortController.abort();
                }
                currentAbortController = new AbortController();

                if (spinner) spinner.style.display = 'inline-block';

                const params = new URLSearchParams();
                if (q) params.set('q', q);
                if (batch && batch !== 'all') params.set('batch', batch);
                if (sem && sem !== 'all') params.set('sem', sem);
                if (type && type !== 'all') params.set('type', type);
                if (division && division !== 'all') params.set('division', division);
                if (page > 1) params.set('page', page);

                const url = '{{ route("examinations.stdmarks") }}?' + params.toString();

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

            // Input listener with 250ms debounce
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    performSearch(1);
                }, 250);
            });

            // Filters
            [filterBatch, filterSem, filterType, filterDivision].forEach(function (select) {
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
                if (filterBatch) filterBatch.value = 'all';
                filterSem.value = 'all';
                filterType.value = 'all';
                filterDivision.value = 'all';
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

            // Keyboard shortcuts
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
