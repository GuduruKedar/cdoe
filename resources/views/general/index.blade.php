@extends('layouts.app')

@section('page_title', 'General Regulations & Documents')

@push('styles')
<style>
    /* ================= GENERAL REGULATIONS HUB STYLING ================= */
    .gen-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }
    .gen-page-header-text h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.3px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .gen-page-header-text p {
        font-size: 13.5px;
        color: var(--text-muted);
        margin: 0;
    }
    .btn-upload-trigger {
        background: var(--theme-gradient);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px var(--theme-glow);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }
    .btn-upload-trigger:hover {
        transform: translateY(-2px);
        filter: brightness(1.08);
        color: #ffffff;
        box-shadow: 0 6px 18px var(--theme-glow);
    }

    /* Program Filter Switcher (All / MBA / BBA) */
    .filter-controls-card {
        background: var(--bg-card);
        border: 1px solid var(--border-card);
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 24px;
        box-shadow: var(--card-shadow);
    }
    .program-tabs-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--border-subtle);
        padding-bottom: 16px;
        margin-bottom: 16px;
    }
    .program-tab-btn {
        padding: 8px 18px;
        border-radius: 999px;
        font-size: 13.5px;
        font-weight: 700;
        border: 1px solid var(--border-subtle);
        background: var(--bg-hover-row);
        color: var(--text-secondary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .program-tab-btn:hover {
        background: var(--bg-card);
        color: var(--text-primary);
        border-color: var(--border-hover);
        transform: translateY(-1px);
    }
    .program-tab-btn.active {
        background: var(--theme-gradient) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px var(--theme-glow);
    }
    .program-tab-btn .badge-count {
        background: rgba(0, 0, 0, 0.08);
        padding: 2px 7px;
        border-radius: 999px;
        font-size: 11px;
    }
    .program-tab-btn.active .badge-count {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* Sub-Categories Pills & Search Row */
    .subcat-search-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .subcat-pills-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .subcat-pill {
        padding: 5px 14px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        background: transparent;
        border: 1px solid var(--border-subtle);
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .subcat-pill:hover {
        background: var(--bg-hover-row);
        color: var(--text-primary);
    }
    .subcat-pill.active {
        background: var(--bg-hover-row);
        color: var(--theme-primary);
        border-color: var(--theme-primary);
        font-weight: 700;
    }

    .search-box-wrap {
        position: relative;
        min-width: 240px;
        max-width: 320px;
        flex: 1;
    }
    .search-box-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 14px;
        pointer-events: none;
    }
    .search-box-input {
        width: 100%;
        height: 38px;
        padding: 8px 12px 8px 36px;
        background: var(--bg-main);
        border: 1px solid var(--border-subtle);
        border-radius: 10px;
        font-size: 13px;
        color: var(--text-primary);
        outline: none;
        transition: all 0.2s ease;
    }
    .search-box-input:focus {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 3px var(--theme-glow);
    }

    /* ================= CARDS GRID ================= */
    .documents-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .doc-card {
        background: var(--bg-card);
        border: 1px solid var(--border-card);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: var(--card-shadow);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        position: relative;
        overflow: hidden;
    }
    .doc-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-shadow-hover);
        border-color: var(--border-hover);
    }

    /* Top Tags & Badges */
    .doc-card-tags {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 14px;
    }
    .doc-program-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 10px;
        border-radius: 6px;
        letter-spacing: 0.3px;
    }
    .badge-program-mba { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .badge-program-bba { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
    .badge-program-common { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }

    .doc-subcat-badge {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
        background: var(--bg-hover-row);
        padding: 2px 8px;
        border-radius: 999px;
        border: 1px solid var(--border-subtle);
    }
    .doc-regcode-tag {
        font-size: 11px;
        font-weight: 800;
        color: #d97706;
        background: #fef3c7;
        border: 1px solid #fde68a;
        padding: 2px 8px;
        border-radius: 6px;
    }

    /* Main Card Content */
    .doc-main-content {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 16px;
    }
    .doc-type-icon {
        font-size: 38px;
        line-height: 1;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .doc-text-wrap {
        flex: 1;
        min-width: 0;
    }
    .doc-card-title {
        font-size: 15.5px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.35;
        margin-bottom: 6px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .doc-card-desc {
        font-size: 12.5px;
        color: var(--text-muted);
        line-height: 1.5;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Card Metadata */
    .doc-meta-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 11.5px;
        color: var(--text-muted);
        padding: 10px 0;
        border-top: 1px solid var(--border-subtle);
        margin-bottom: 14px;
    }
    .doc-meta-bar span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Card Action Buttons */
    .doc-actions-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-quick-view {
        flex: 1;
        height: 36px;
        background: var(--bg-hover-row);
        border: 1px solid var(--border-subtle);
        color: var(--text-primary);
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.18s ease;
        text-decoration: none;
    }
    .btn-quick-view:hover {
        background: var(--theme-primary);
        border-color: var(--theme-primary);
        color: #ffffff;
    }
    .btn-card-download {
        height: 36px;
        padding: 0 14px;
        background: var(--theme-gradient);
        border: none;
        color: #ffffff;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.18s ease;
        text-decoration: none;
        box-shadow: 0 2px 8px var(--theme-glow);
    }
    .btn-card-download:hover {
        filter: brightness(1.1);
        color: #ffffff;
    }
    .btn-card-delete {
        width: 36px;
        height: 36px;
        background: transparent;
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #ef4444;
        border-radius: 10px;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-card-delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #b91c1c;
    }

    /* Empty state */
    .empty-state-card {
        grid-column: 1 / -1;
        background: var(--bg-card);
        border: 1.5px dashed var(--border-subtle);
        border-radius: 16px;
        padding: 48px 20px;
        text-align: center;
    }
    .empty-state-card i {
        font-size: 46px;
        color: var(--text-muted);
        opacity: 0.6;
        margin-bottom: 12px;
        display: inline-block;
    }
    .empty-state-card h3 {
        font-size: 17px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 6px;
    }
    .empty-state-card p {
        font-size: 13.5px;
        color: var(--text-muted);
        max-width: 440px;
        margin: 0 auto;
    }
</style>
@endpush

@section('content')

{{-- Alert Messages --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 12px;">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 12px;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Header Banner --}}
<div class="gen-page-header">
    <div class="gen-page-header-text">
        <h1>
            <i class="bi bi-folder2-open text-primary"></i>
            <span>Regulations &amp; Academic Documents</span>
        </h1>
        <p>Curriculum regulations, course syllabi, and official university documentation for Online MBA &amp; BBA.</p>
    </div>

    @if ($isMainAdmin)
        <button type="button" class="btn-upload-trigger" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
            <i class="bi bi-cloud-arrow-up-fill"></i>
            <span>Upload Regulation / Document</span>
        </button>
    @endif
</div>

{{-- Filter & Control Card --}}
<div class="filter-controls-card">

    {{-- Program Selection Tabs (All / MBA / BBA) --}}
    <div class="program-tabs-bar">
        <a href="{{ route('general.index', ['program' => 'ALL', 'sub' => $activeSubCategory]) }}"
           class="program-tab-btn {{ $activeProgram === 'ALL' ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i>
            <span>All Programs</span>
            <span class="badge-count">{{ $counts['ALL'] ?? 0 }}</span>
        </a>

        <a href="{{ route('general.index', ['program' => 'MBA', 'sub' => $activeSubCategory]) }}"
           class="program-tab-btn {{ $activeProgram === 'MBA' ? 'active' : '' }}">
            <i class="bi bi-mortarboard-fill"></i>
            <span>Online MBA</span>
            <span class="badge-count">{{ $counts['MBA'] ?? 0 }}</span>
        </a>

        <a href="{{ route('general.index', ['program' => 'BBA', 'sub' => $activeSubCategory]) }}"
           class="program-tab-btn {{ $activeProgram === 'BBA' ? 'active' : '' }}">
            <i class="bi bi-book-fill"></i>
            <span>Online BBA</span>
            <span class="badge-count">{{ $counts['BBA'] ?? 0 }}</span>
        </a>
    </div>

    {{-- Subcategories Pills & Live Search --}}
    <div class="subcat-search-bar">
        <div class="subcat-pills-row">
            <a href="{{ route('general.index', ['program' => $activeProgram, 'sub' => 'All', 'q' => $search]) }}"
               class="subcat-pill {{ $activeSubCategory === 'All' ? 'active' : '' }}">
                All Categories
            </a>

            @foreach ($allSubCategories as $cat)
                <a href="{{ route('general.index', ['program' => $activeProgram, 'sub' => $cat, 'q' => $search]) }}"
                   class="subcat-pill {{ $activeSubCategory === $cat ? 'active' : '' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        {{-- Search Input --}}
        <form method="GET" action="{{ route('general.index') }}" class="search-box-wrap m-0 p-0">
            <input type="hidden" name="program" value="{{ $activeProgram }}">
            <input type="hidden" name="sub" value="{{ $activeSubCategory }}">
            <i class="bi bi-search"></i>
            <input type="text"
                   name="q"
                   id="searchDocsInput"
                   class="search-box-input"
                   placeholder="Search regulations, code (R22)..."
                   value="{{ $search }}"
                   onkeyup="filterCardsClientSide(this.value)">
        </form>
    </div>

</div>

{{-- Cards Grid --}}
<div class="documents-cards-grid" id="documentsCardsContainer">

    @forelse ($documents as $doc)
        <div class="doc-card"
             data-title="{{ strtolower($doc->title) }}"
             data-code="{{ strtolower($doc->regulation_code ?? '') }}"
             data-program="{{ strtolower($doc->program) }}"
             data-cat="{{ strtolower($doc->sub_category) }}">

            <div>
                {{-- Tags Bar --}}
                <div class="doc-card-tags">
                    <span class="doc-program-badge {{ $doc->program_badge_class }}">
                        {{ $doc->program }}
                    </span>

                    <div class="d-flex align-items-center gap-1">
                        @if ($doc->regulation_code)
                            <span class="doc-regcode-tag">
                                {{ $doc->regulation_code }}
                            </span>
                        @endif
                        <span class="doc-subcat-badge">
                            {{ $doc->sub_category }}
                        </span>
                    </div>
                </div>

                {{-- Main Title & Description --}}
                <div class="doc-main-content">
                    <i class="{{ $doc->file_icon }} doc-type-icon"></i>
                    <div class="doc-text-wrap">
                        <h2 class="doc-card-title" title="{{ $doc->title }}">
                            {{ $doc->title }}
                        </h2>
                        @if ($doc->description)
                            <p class="doc-card-desc">
                                {{ $doc->description }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                {{-- Metadata Bar --}}
                <div class="doc-meta-bar">
                    <span>
                        <i class="bi bi-calendar3"></i>
                        {{ $doc->created_at ? $doc->created_at->format('d M Y') : 'Recent' }}
                    </span>
                    <span>•</span>
                    <span>
                        <i class="bi bi-hdd-network"></i>
                        {{ $doc->formatted_size }}
                    </span>
                    <span>•</span>
                    <span>
                        <i class="bi bi-download"></i>
                        {{ $doc->downloads_count }} downloads
                    </span>
                </div>

                {{-- Action Buttons --}}
                <div class="doc-actions-row">
                    {{-- Quick View Button --}}
                    <button type="button"
                            class="btn-quick-view"
                            onclick="openQuickViewModal('{{ $doc->title }}', '{{ route('general.preview', $doc) }}', '{{ $doc->file_type }}', '{{ route('general.download', $doc) }}', '{{ $doc->formatted_size }}')">
                        <i class="bi bi-eye-fill"></i>
                        <span>Quick View</span>
                    </button>

                    {{-- Download Button --}}
                    <a href="{{ route('general.download', $doc) }}" class="btn-card-download" title="Download {{ $doc->file_name }}">
                        <i class="bi bi-download"></i>
                        <span>Download</span>
                    </a>

                    {{-- Delete Button (Main Admin Only) --}}
                    @if ($isMainAdmin)
                        <form method="POST"
                              action="{{ route('general.destroy', $doc) }}"
                              class="m-0 p-0"
                              onsubmit="return confirm('Are you sure you want to delete this document: {{ addslashes($doc->title) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-card-delete" title="Delete document">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    @empty
        <div class="empty-state-card">
            <i class="bi bi-folder-x"></i>
            <h3>No Regulations or Documents Found</h3>
            <p>
                No documents match the selected program or category.
                @if ($isMainAdmin)
                    Click the <strong>Upload Regulation</strong> button above to publish new documents.
                @endif
            </p>
        </div>
    @endforelse

</div>

{{-- ================= MODAL: QUICK VIEW / PREVIEW ================= --}}
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-height: 92vh;">
        <div class="modal-content" style="border-radius: 18px; overflow: hidden; border: 1px solid var(--border-card);">
            <div class="modal-header d-flex align-items-center justify-content-between" style="background: var(--bg-hover-row); border-bottom: 1px solid var(--border-subtle); padding: 14px 20px;">
                <h5 class="modal-title fw-bold" id="quickViewModalLabel" style="font-size: 16px; color: var(--text-primary);">
                    <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>
                    <span id="quickViewTitle">Document Preview</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="quickViewDownloadBtn" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-download"></i> Download (<span id="quickViewSize"></span>)
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0" id="quickViewContainer" style="height: 75vh; background: #525659; display: flex; align-items: center; justify-content: center;">
                {{-- Dynamically populated iframe or notice --}}
            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL: UPLOAD REGULATION (Main Admin Only) ================= --}}
@if ($isMainAdmin)
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: 1px solid var(--border-card); overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
            
            <form method="POST" action="{{ route('general.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="modal-header" style="background: var(--bg-hover-row); border-bottom: 1px solid var(--border-subtle); padding: 18px 24px;">
                    <h5 class="modal-title fw-bold" id="uploadDocModalLabel" style="font-size: 18px; color: var(--text-primary);">
                        <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>
                        Upload Regulation &amp; Document
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 24px;">

                    {{-- 1. Program Selection --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13.5px; color: var(--text-primary);">
                            1. Select Program <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="program" id="progMBA" value="MBA" checked>
                                <label class="form-check-label fw-semibold" for="progMBA">Online MBA</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="program" id="progBBA" value="BBA">
                                <label class="form-check-label fw-semibold" for="progBBA">Online BBA</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="program" id="progCommon" value="COMMON">
                                <label class="form-check-label fw-semibold" for="progCommon">Common (Both MBA &amp; BBA)</label>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Sub-Category (Dynamic creation supported) --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="sub_category" class="form-label fw-bold" style="font-size: 13.5px; color: var(--text-primary);">
                                2. Sub-Category / Nav Section <span class="text-danger">*</span>
                            </label>
                            <select name="sub_category" id="sub_category" class="form-select" onchange="checkCustomSubCategory(this.value)">
                                <option value="Regulations">Regulations</option>
                                <option value="Syllabus">Syllabus</option>
                                <option value="Exam Guidelines">Exam Guidelines</option>
                                <option value="Academic Calendar">Academic Calendar</option>
                                <option value="Curriculum">Curriculum</option>
                                @foreach ($allSubCategories as $extraCat)
                                    @if (!in_array($extraCat, ['Regulations', 'Syllabus', 'Exam Guidelines', 'Academic Calendar', 'Curriculum']))
                                        <option value="{{ $extraCat }}">{{ $extraCat }}</option>
                                    @endif
                                @endforeach
                                <option value="__NEW__">+ Create New Sub-Category...</option>
                            </select>
                        </div>

                        {{-- Dynamic New Sub-Category Input --}}
                        <div class="col-md-6" id="newSubCatGroup" style="display: none;">
                            <label for="new_sub_category" class="form-label fw-bold" style="font-size: 13.5px; color: var(--text-primary);">
                                New Sub-Category Name
                            </label>
                            <input type="text"
                                   name="new_sub_category"
                                   id="new_sub_category"
                                   class="form-control"
                                   placeholder="e.g. Internship Guidelines, Case Studies">
                        </div>

                        <div class="col-md-6" id="regCodeGroup">
                            <label for="regulation_code" class="form-label fw-bold" style="font-size: 13.5px; color: var(--text-primary);">
                                Regulation Code (Optional)
                            </label>
                            <input type="text"
                                   name="regulation_code"
                                   id="regulation_code"
                                   class="form-control"
                                   placeholder="e.g. R22, R21, R24">
                        </div>
                    </div>

                    {{-- 3. Document Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold" style="font-size: 13.5px; color: var(--text-primary);">
                            3. Document Title <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               id="title"
                               class="form-control"
                               placeholder="e.g. R22 MBA Academic Regulations &amp; Promotion Criteria"
                               required>
                    </div>

                    {{-- 4. Description --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold" style="font-size: 13.5px; color: var(--text-primary);">
                            4. Description / Summary
                        </label>
                        <textarea name="description"
                                  id="description"
                                  rows="2"
                                  class="form-control"
                                  placeholder="Brief overview of what this document covers..."></textarea>
                    </div>

                    {{-- 5. File Upload (Any document type supported) --}}
                    <div class="mb-2">
                        <label for="document" class="form-label fw-bold" style="font-size: 13.5px; color: var(--text-primary);">
                            5. Attach Document File (Any Type: PDF, DOC, DOCX, ZIP, XLS, etc.) <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                               name="document"
                               id="document"
                               class="form-control"
                               required>
                        <div class="form-text" style="font-size: 12px; color: var(--text-muted);">
                            Supports all formats: PDF, Word (.doc, .docx), Excel (.xls, .xlsx), PowerPoint (.ppt, .pptx), ZIP, Images. Max 50 MB.
                        </div>
                    </div>

                </div>

                <div class="modal-footer" style="background: var(--bg-hover-row); border-top: 1px solid var(--border-subtle); padding: 14px 24px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span>Upload &amp; Publish</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // Quick View modal logic
    function openQuickViewModal(title, previewUrl, fileType, downloadUrl, sizeText) {
        document.getElementById('quickViewTitle').textContent = title;
        document.getElementById('quickViewSize').textContent = sizeText;
        document.getElementById('quickViewDownloadBtn').href = downloadUrl;

        const container = document.getElementById('quickViewContainer');
        container.innerHTML = '';

        const type = (fileType || '').toLowerCase();
        if (type === 'pdf') {
            container.innerHTML = `<iframe src="${previewUrl}#toolbar=1" style="width: 100%; height: 100%; border: none;"></iframe>`;
        } else if (['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'].includes(type)) {
            container.innerHTML = `<div style="overflow: auto; max-height: 100%; padding: 20px; text-align: center;"><img src="${previewUrl}" alt="${title}" style="max-width: 100%; max-height: 70vh; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);"></div>`;
        } else {
            container.innerHTML = `
                <div style="text-align: center; color: #ffffff; padding: 40px 20px;">
                    <i class="bi bi-file-earmark-arrow-down" style="font-size: 64px; opacity: 0.85;"></i>
                    <h4 class="mt-3 fw-bold">${title}</h4>
                    <p style="color: #cbd5e1; font-size: 14px; max-width: 440px; margin: 8px auto 20px;">
                        This document (${type.toUpperCase()} file) can be downloaded directly to your device for viewing.
                    </p>
                    <a href="${downloadUrl}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2" style="border-radius: 10px; font-weight: 700;">
                        <i class="bi bi-download"></i> Download File Now
                    </a>
                </div>
            `;
        }

        const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        modal.show();
    }

    // Toggle custom dynamic subcategory input
    function checkCustomSubCategory(val) {
        const group = document.getElementById('newSubCatGroup');
        const input = document.getElementById('new_sub_category');
        if (val === '__NEW__') {
            group.style.display = 'block';
            input.required = true;
            input.focus();
        } else {
            group.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    // Client-side instant filter
    function filterCardsClientSide(query) {
        const q = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.doc-card');

        cards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const code = card.getAttribute('data-code') || '';
            const desc = card.querySelector('.doc-card-desc') ? card.querySelector('.doc-card-desc').textContent.toLowerCase() : '';

            if (!q || title.includes(q) || code.includes(q) || desc.includes(q)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
