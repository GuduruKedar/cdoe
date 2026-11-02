@extends('layouts.app')

@section('page_title', 'Manage Regulations & PPR Details — CDOE')
@section('hideTitle', 'true')
@section('hidePageSub', 'true')

@push('styles')
<style>
    /* Manage Page Modern Styling */
    .manage-hero-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .manage-hero-header h1 {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.3px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .manage-hero-header p {
        font-size: 13.5px;
        color: var(--text-muted);
        margin: 0;
    }

    /* Modern Card Container */
    .custom-manage-card {
        background: var(--bg-card);
        border: 1px solid var(--border-card);
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        padding: 24px;
        margin-top: 0;
        transition: box-shadow 0.2s ease;
    }
    .custom-manage-card:hover {
        box-shadow: var(--card-shadow-hover);
    }
    .custom-card-title {
        font-size: 16.5px;
        font-weight: 800;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-subtle);
    }

    /* Program Segmented Radio Selector */
    .prog-pill-radio-wrap {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }
    .prog-pill-radio-wrap input[type="radio"] {
        display: none;
    }
    .prog-pill-radio-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 10px 6px;
        border-radius: 12px;
        border: 1.5px solid var(--border-subtle);
        background: var(--bg-hover-row);
        color: var(--text-secondary);
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
        user-select: none;
    }
    .prog-pill-radio-label i {
        font-size: 18px;
        margin-bottom: 4px;
        color: var(--text-muted);
        transition: color 0.2s ease;
    }
    .prog-pill-radio-wrap input[type="radio"]:checked + .prog-pill-radio-label {
        border-color: var(--theme-primary);
        background: rgba(var(--theme-primary-rgb, 13, 110, 253), 0.08);
        color: var(--theme-primary);
        box-shadow: 0 4px 12px rgba(var(--theme-primary-rgb, 13, 110, 253), 0.15);
        transform: translateY(-1px);
    }
    .prog-pill-radio-wrap input[type="radio"]:checked + .prog-pill-radio-label i {
        color: var(--theme-primary);
    }
    .prog-pill-radio-label:hover {
        border-color: var(--theme-primary);
        color: var(--theme-primary);
    }

    /* Preset Badges */
    .month-preset-btn {
        background: var(--bg-hover-row);
        border: 1px solid var(--border-subtle);
        color: var(--text-secondary);
        border-radius: 8px;
        padding: 3px 9px;
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .month-preset-btn:hover {
        background: var(--theme-primary);
        color: #ffffff;
        border-color: var(--theme-primary);
        transform: translateY(-1px);
    }

    /* Modern Table Refinements */
    .table-modern-ppr {
        margin-bottom: 0;
        vertical-align: middle;
        font-size: 12.5px;
    }
    .table-modern-ppr thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 14px;
        white-space: nowrap;
        border-bottom: 1.5px solid var(--border-subtle);
    }
    [data-theme="dark"] .table-modern-ppr thead th {
        background: #1e293b;
        color: #94a3b8;
    }
    .table-modern-ppr tbody td {
        padding: 13px 14px;
        border-bottom: 1px solid var(--border-subtle);
    }
    .table-modern-ppr tbody tr:hover {
        background: var(--bg-hover-row);
    }

    /* Badge Themes */
    .badge-prog-mca { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; font-weight: 700; }
    .badge-prog-mba { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; font-weight: 700; }
    .badge-prog-bca { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; font-weight: 700; }
    .badge-prog-bba { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; font-weight: 700; }

    .badge-regcode-gold {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
        font-weight: 800;
        letter-spacing: 0.5px;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .badge-monthyear-soft {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
        font-weight: 700;
        padding: 4px 9px;
        border-radius: 6px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Action Buttons in Table */
    .btn-action-pill {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: all 0.15s ease;
        padding: 0;
    }
    .btn-action-pill:hover {
        transform: translateY(-2px);
    }

    /* Regulation Name cell with clean wrap */
    .regname-title-cell {
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.4;
        word-break: normal;
        overflow-wrap: break-word;
        min-width: 170px;
        max-width: 240px;
    }
</style>
@endpush

@section('content')

{{-- Alert Messages --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <span class="fw-semibold">{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <span class="fw-semibold">{{ session('error') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center gap-2 fw-bold mb-2">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <span>Please correct the errors below:</span>
        </div>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Single-Line Page Header --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div class="d-flex align-items-center gap-2">
        <h1 class="h4 fw-bold text-primary mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-gear-wide-connected"></i>
            <span>Manage Regulations &amp; PPR Books</span>
        </h1>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('general.pprbooks') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 700; padding: 7px 16px; font-size: 13.5px;">
            <i class="bi bi-grid-fill text-primary"></i>
            <span>All Regulations (Cards View)</span>
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- Left Column: Upload New Regulation Form --}}
    <div class="col-12 col-xl-4 col-lg-5">
        <div class="custom-manage-card">
            <div class="custom-card-title">
                <i class="bi bi-cloud-arrow-up-fill text-primary"></i>
                <span>Upload New Regulation</span>
            </div>

            <form method="POST" action="{{ route('general.store') }}" enctype="multipart/form-data" id="uploadRegulationForm">
                @csrf

                {{-- 1. Program Selection: Dropdown Type --}}
                <div class="mb-3">
                    <label for="m_prog_name" class="form-label fw-bold small text-secondary d-flex align-items-center justify-content-between">
                        <span>Program <span class="text-danger">*</span></span>
                        <span class="text-muted" style="font-size: 11px;">Select Degree</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-mortarboard-fill"></i>
                        </span>
                        <select name="prog_name" id="m_prog_name" class="form-select fw-semibold" style="border-radius: 0 10px 10px 0;" required onchange="syncRegnameSuggestion()">
                            <option value="" disabled {{ old('prog_name') ? '' : 'selected' }}>-- Select Program --</option>
                            <option value="MCA" {{ old('prog_name', 'MCA') === 'MCA' ? 'selected' : '' }}>MCA (Master of Computer Applications)</option>
                            <option value="MBA" {{ old('prog_name') === 'MBA' ? 'selected' : '' }}>MBA (Master of Business Administration)</option>
                            <option value="BCA" {{ old('prog_name') === 'BCA' ? 'selected' : '' }}>BCA (Bachelor of Computer Applications)</option>
                            <option value="BBA" {{ old('prog_name') === 'BBA' ? 'selected' : '' }}>BBA (Bachelor of Business Administration)</option>
                        </select>
                    </div>
                </div>

                {{-- 2. Regulation Code --}}
                <div class="mb-3">
                    <label for="m_regcode" class="form-label fw-bold small text-secondary d-flex align-items-center justify-content-between">
                        <span>Regulation Code <span class="text-danger">*</span></span>
                        <span class="text-muted" style="font-size: 11px;">e.g. R22, R21</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary fw-bold" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-tag-fill me-1"></i>
                        </span>
                        <input type="text"
                               name="regcode"
                               id="m_regcode"
                               class="form-control text-uppercase fw-bold"
                               style="border-radius: 0 10px 10px 0; letter-spacing: 0.5px;"
                               placeholder="R22"
                               maxlength="3"
                               required
                               oninput="formatManageRegulationCode(this)"
                               value="{{ old('regcode', 'R22') }}">
                    </div>
                    <div class="form-text small" style="font-size: 11px;">
                        Must be 3 characters starting with R (e.g. <code>r22</code> &rarr; <code>R22</code>).
                    </div>
                </div>

                {{-- 3. Month & Year (Jan 2022, July 2022, etc.) --}}
                <div class="mb-3">
                    <label for="m_month_year" class="form-label fw-bold small text-secondary d-flex align-items-center justify-content-between">
                        <span>Month &amp; Year</span>
                        <span class="text-muted" style="font-size: 11px;">Dynamic or Preset</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-primary" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-calendar3"></i>
                        </span>
                        <input type="text"
                               name="month_year"
                               id="m_month_year"
                               class="form-control fw-semibold"
                               style="border-radius: 0 10px 10px 0;"
                               placeholder="e.g. Jan 2022, July 2022"
                               list="monthYearListManage"
                               value="{{ old('month_year') }}">
                    </div>
                    <datalist id="monthYearListManage">
                        @foreach ($monthYears ?? ['Jan 2022', 'July 2022', 'Jan 2023', 'July 2023', 'Jan 2024', 'July 2024', 'Jan 2025', 'July 2025'] as $my)
                            <option value="{{ $my }}">
                        @endforeach
                    </datalist>

                    {{-- Clickable Quick Presets --}}
                    <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                        <span class="text-muted small me-1" style="font-size: 11px; font-weight: 600;">Presets:</span>
                        <button type="button" class="month-preset-btn" onclick="setManageMonthYear('Jan 2022')">Jan 2022</button>
                        <button type="button" class="month-preset-btn" onclick="setManageMonthYear('July 2022')">July 2022</button>
                        <button type="button" class="month-preset-btn" onclick="setManageMonthYear('Jan 2023')">Jan 2023</button>
                        <button type="button" class="month-preset-btn" onclick="setManageMonthYear('July 2023')">July 2023</button>
                        <button type="button" class="month-preset-btn" onclick="setManageMonthYear('Jan 2024')">Jan 2024</button>
                        <button type="button" class="month-preset-btn" onclick="setManageMonthYear('July 2024')">July 2024</button>
                    </div>
                </div>

                {{-- 4. Regulation Name (regname) --}}
                <div class="mb-3">
                    <label for="m_regname" class="form-label fw-bold small text-secondary">
                        Regulation Name (regname) <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="regname"
                           id="m_regname"
                           class="form-control"
                           style="border-radius: 10px;"
                           placeholder="e.g. R22_MBA - GENERAL"
                           value="{{ old('regname') }}"
                           required>
                </div>

                {{-- 5. Description --}}
                <div class="mb-3">
                    <label for="m_description" class="form-label fw-bold small text-secondary">
                        Description / Summary
                    </label>
                    <textarea name="description"
                              id="m_description"
                              rows="2"
                              class="form-control"
                              style="border-radius: 10px; font-size: 13px;"
                              placeholder="Brief summary or specialisation details...">{{ old('description') }}</textarea>
                </div>

                {{-- 6. File Upload --}}
                <div class="mb-4">
                    <label for="m_document" class="form-label fw-bold small text-secondary">
                        Upload Regulation Document <span class="text-danger">*</span>
                    </label>
                    <input type="file"
                           name="document"
                           id="m_document"
                           class="form-control"
                           style="border-radius: 10px;"
                           required>
                    <div class="form-text small" style="font-size: 11px;">
                        PDF, Word (.docx), Excel, PPT, ZIP, or Images. Max 50 MB.
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 py-2" style="border-radius: 12px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 14px rgba(13, 110, 253, 0.25);">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                    <span>Upload &amp; Save Regulation</span>
                </button>

            </form>
        </div>
    </div>

    {{-- Right Column: Regulations List Table --}}
    <div class="col-12 col-xl-8 col-lg-7">
        <div class="custom-manage-card">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="custom-card-title mb-0 border-0 p-0">
                        <i class="bi bi-table text-primary"></i>
                        <span>Regulations List</span>
                        <span class="badge bg-primary rounded-pill px-2 py-1 ms-1" style="font-size: 11.5px;">{{ $documents->total() }}</span>
                    </div>
                </div>
            </div>

            {{-- Table with Responsive Wrapper and Clean Sizing --}}
            <div class="table-responsive rounded-3 border" style="background: var(--bg-card); overflow-x: auto;">
                <table class="table table-modern-ppr table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">S.No</th>
                            <th style="width: 110px;">Program</th>
                            <th style="width: 80px;">Reg Code</th>
                            <th style="width: 120px;">Month &amp; Year</th>
                            <th style="min-width: 180px;">Regulation Name</th>
                            <th style="min-width: 180px;">Document File</th>
                            <th style="min-width: 130px;">Description</th>
                            <th class="text-end" style="width: 110px; white-space: nowrap;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $item)
                            <tr>
                                {{-- S.no --}}
                                <td class="text-center fw-bold text-muted">
                                    #{{ $item->s_no }}
                                </td>

                                {{-- Program (Name & ID) --}}
                                <td>
                                    @php
                                        $progLower = strtolower($item->prog_name);
                                        $badgeClass = match($progLower) {
                                            'mca' => 'badge-prog-mca',
                                            'mba' => 'badge-prog-mba',
                                            'bca' => 'badge-prog-bca',
                                            'bba' => 'badge-prog-bba',
                                            default => 'bg-secondary text-white'
                                        };
                                    @endphp
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $item->prog_name }}
                                        </span>
                                        <span class="badge bg-light text-muted border" style="font-size: 10px;" title="Prog ID: {{ $item->prog_id }}">
                                            ID: {{ $item->prog_id ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Regcode --}}
                                <td>
                                    <span class="badge-regcode-gold">
                                        {{ $item->regcode }}
                                    </span>
                                </td>

                                {{-- Month & Year --}}
                                <td>
                                    @if ($item->month_year)
                                        <span class="badge-monthyear-soft">
                                            <i class="bi bi-calendar3"></i>
                                            <span>{{ $item->month_year }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted small fst-italic">Not set</span>
                                    @endif
                                </td>

                                {{-- regname --}}
                                <td>
                                    <div class="regname-title-cell">
                                        {{ $item->regname }}
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size: 10.5px;">
                                        <i class="bi bi-clock me-1"></i>{{ $item->created_at ? $item->created_at->format('d M Y, h:i A') : 'Recent' }}
                                    </div>
                                </td>

                                {{-- filename & filepath --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="{{ $item->file_icon }}" style="font-size: 20px;"></i>
                                        <div style="min-width: 0;">
                                            <div class="fw-semibold text-truncate" style="max-width: 160px;" title="{{ $item->filename }}">
                                                {{ $item->filename }}
                                            </div>
                                            <div class="text-muted" style="font-size: 10px;">
                                                <span>{{ $item->formatted_size }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- description --}}
                                <td>
                                    <div class="text-muted text-truncate" style="max-width: 140px; font-size: 12px;" title="{{ $item->description }}">
                                        {{ $item->description ?: '—' }}
                                    </div>
                                </td>

                                {{-- Actions: Download, Edit (Re-edit details), Delete --}}
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        {{-- Download --}}
                                        <a href="{{ route('general.download', $item) }}"
                                           class="btn btn-sm btn-outline-primary btn-action-pill"
                                           title="Download Document">
                                            <i class="bi bi-download"></i>
                                        </a>

                                        {{-- Edit Details Modal Trigger --}}
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary btn-action-pill"
                                                title="Re-edit Details"
                                                onclick="openEditRegulationModal({{ json_encode([
                                                    's_no' => $item->s_no,
                                                    'prog_name' => $item->prog_name,
                                                    'prog_id' => $item->prog_id,
                                                    'regcode' => $item->regcode,
                                                    'month_year' => $item->month_year,
                                                    'regname' => $item->regname,
                                                    'description' => $item->description,
                                                    'filename' => $item->filename,
                                                ]) }})">
                                            <i class="bi bi-pencil-square text-dark"></i>
                                        </button>

                                        {{-- Delete --}}
                                        <form method="POST"
                                              action="{{ route('general.destroy', $item) }}"
                                              class="d-inline m-0 p-0"
                                              onsubmit="return confirm('Are you sure you want to delete regulation #{{ $item->s_no }} ({{ addslashes($item->regname) }})?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger btn-action-pill"
                                                    title="Delete Regulation">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                    <span class="fw-semibold">No regulations found.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            <div class="mt-3">
                {{ $documents->links() }}
            </div>
        </div>
    </div>

</div>

{{-- ================= RE-EDIT DETAILS MODAL ================= --}}
<div class="modal fade" id="editRegulationModal" tabindex="-1" aria-labelledby="editRegulationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 18px; border: 1px solid var(--border-card); box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="editRegulationModalLabel">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span>Re-Edit Regulation Details</span>
                    <span class="badge bg-secondary ms-2" id="editModalSnoBadge">#</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" id="editRegulationForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Program Selection Dropdown --}}
                        <div class="col-12">
                            <label for="edit_prog_name" class="form-label fw-bold small text-secondary">
                                Program <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </span>
                                <select name="prog_name" id="edit_prog_name" class="form-select fw-semibold" required>
                                    <option value="MCA">MCA (Master of Computer Applications)</option>
                                    <option value="MBA">MBA (Master of Business Administration)</option>
                                    <option value="BCA">BCA (Bachelor of Computer Applications)</option>
                                    <option value="BBA">BBA (Bachelor of Business Administration)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Regulation Code --}}
                        <div class="col-md-6">
                            <label for="edit_regcode" class="form-label fw-bold small text-secondary">
                                Regulation Code <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary fw-bold">
                                    <i class="bi bi-tag-fill me-1"></i>
                                </span>
                                <input type="text"
                                       name="regcode"
                                       id="edit_regcode"
                                       class="form-control text-uppercase fw-bold"
                                       placeholder="R22"
                                       maxlength="3"
                                       required
                                       oninput="formatManageRegulationCode(this)">
                            </div>
                            <div class="form-text small" style="font-size: 11px;">Must be 3 chars starting with R (e.g. R22).</div>
                        </div>

                        {{-- Month & Year --}}
                        <div class="col-md-6">
                            <label for="edit_month_year" class="form-label fw-bold small text-secondary">
                                Month &amp; Year
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-calendar3"></i>
                                </span>
                                <input type="text"
                                       name="month_year"
                                       id="edit_month_year"
                                       class="form-control fw-semibold"
                                       placeholder="e.g. Jan 2022, July 2022"
                                       list="monthYearListManage">
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <button type="button" class="month-preset-btn py-0 px-2" style="font-size: 11px;" onclick="setEditMonthYear('Jan 2022')">Jan 2022</button>
                                <button type="button" class="month-preset-btn py-0 px-2" style="font-size: 11px;" onclick="setEditMonthYear('July 2022')">July 2022</button>
                                <button type="button" class="month-preset-btn py-0 px-2" style="font-size: 11px;" onclick="setEditMonthYear('Jan 2023')">Jan 2023</button>
                                <button type="button" class="month-preset-btn py-0 px-2" style="font-size: 11px;" onclick="setEditMonthYear('July 2023')">July 2023</button>
                            </div>
                        </div>

                        {{-- Regulation Name --}}
                        <div class="col-12">
                            <label for="edit_regname" class="form-label fw-bold small text-secondary">
                                Regulation Name (regname) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="regname"
                                   id="edit_regname"
                                   class="form-control"
                                   required>
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label for="edit_description" class="form-label fw-bold small text-secondary">
                                Description / Notes
                            </label>
                            <textarea name="description"
                                      id="edit_description"
                                      rows="2"
                                      class="form-control"></textarea>
                        </div>

                        {{-- Optional Replace File --}}
                        <div class="col-12">
                            <label for="edit_document" class="form-label fw-bold small text-secondary d-flex align-items-center justify-content-between">
                                <span>Replace Document (Optional)</span>
                                <span class="text-muted" style="font-size: 11px;">Current: <code id="editCurrentFilename">file.pdf</code></span>
                            </label>
                            <input type="file"
                                   name="document"
                                   id="edit_document"
                                   class="form-control">
                            <div class="form-text small" style="font-size: 11px;">
                                Leave empty to keep existing file. Uploading a new file will replace the current file in storage.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600;">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 700; padding: 8px 22px;">
                        <i class="bi bi-check2-circle"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function formatManageRegulationCode(input) {
        let val = (input.value || '').toUpperCase().trim();
        if (val.length > 0 && !val.startsWith('R')) {
            val = 'R' + val.replace(/^R+/i, '');
        }
        if (val.length > 3) {
            val = val.substring(0, 3);
        }
        input.value = val;
    }

    function setManageMonthYear(val) {
        const input = document.getElementById('m_month_year');
        if (input) {
            input.value = val;
            input.focus();
        }
    }

    function setEditMonthYear(val) {
        const input = document.getElementById('edit_month_year');
        if (input) {
            input.value = val;
            input.focus();
        }
    }

    function syncRegnameSuggestion() {
        const regcode = document.getElementById('m_regcode').value.trim() || 'R22';
        const prog = document.getElementById('m_prog_name')?.value || 'MCA';
        const regnameInput = document.getElementById('m_regname');
        if (!regnameInput.value || regnameInput.dataset.touched !== 'true') {
            regnameInput.placeholder = `e.g. ${regcode}_${prog} - ACADEMIC REGULATIONS`;
        }
    }

    function openEditRegulationModal(data) {
        document.getElementById('editModalSnoBadge').innerText = '#' + data.s_no;
        const form = document.getElementById('editRegulationForm');
        form.action = `/general/document/${data.s_no}`;

        // Select program dropdown
        const select = document.getElementById('edit_prog_name');
        if (select) {
            select.value = data.prog_name;
        }

        // Fill fields
        document.getElementById('edit_regcode').value = data.regcode || '';
        document.getElementById('edit_month_year').value = data.month_year || '';
        document.getElementById('edit_regname').value = data.regname || '';
        document.getElementById('edit_description').value = data.description || '';
        document.getElementById('editCurrentFilename').innerText = data.filename || 'None';

        // Clear file input
        const fileInput = document.getElementById('edit_document');
        if (fileInput) {
            fileInput.value = '';
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editRegulationModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const regnameInput = document.getElementById('m_regname');
        if (regnameInput) {
            regnameInput.addEventListener('input', function() {
                this.dataset.touched = 'true';
            });
        }
    });
</script>
@endpush
