@extends('layouts.app')

@section('page_title', 'Manage Regulations & PPR Details')

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

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center gap-2 fw-bold mb-1">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Please check the form inputs:</span>
        </div>
        <ul class="mb-0 ps-3 small">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-gear-wide-connected me-2"></i>
            Manage Regulations (PPR Details)
        </h1>
        <p class="text-muted small mb-0">Main Admin portal for uploading, editing, and managing regulations stored in <code>visa.ppr_details</code>.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('general.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 600;">
            <i class="bi bi-grid"></i>
            <span>All Regulations (Cards View)</span>
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- Left Column: Upload New Regulation Form --}}
    <div class="col-lg-4">
        <div class="card p-4" style="border-radius: 16px; margin-top: 0; box-shadow: var(--card-shadow); border: 1px solid var(--border-card);">
            <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2" style="color: var(--text-primary);">
                <i class="bi bi-cloud-arrow-up-fill text-primary"></i>
                Upload Regulation
            </h2>

            <form method="POST" action="{{ route('general.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- 1. Program Selection: MCA, BBA, BCA, MBA --}}
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">
                        Program <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prog_name" id="mProgMCA" value="MCA" {{ old('prog_name') === 'MCA' ? 'checked' : '' }} checked>
                            <label class="form-check-label fw-semibold small" for="mProgMCA">MCA</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prog_name" id="mProgBBA" value="BBA" {{ old('prog_name') === 'BBA' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="mProgBBA">BBA</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prog_name" id="mProgBCA" value="BCA" {{ old('prog_name') === 'BCA' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="mProgBCA">BCA</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="prog_name" id="mProgMBA" value="MBA" {{ old('prog_name') === 'MBA' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold small" for="mProgMBA">MBA</label>
                        </div>
                    </div>
                </div>

                {{-- 2. Regulation Code (Validation: auto lowercase 'r' -> 'R', strictly 3 chars, 1st char R) --}}
                <div class="mb-3">
                    <label for="m_regcode" class="form-label fw-bold small text-secondary">
                        Regulation Code <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text fw-bold text-primary">R</span>
                        <input type="text"
                               name="regcode"
                               id="m_regcode"
                               class="form-control form-control-sm text-uppercase fw-bold"
                               placeholder="R22"
                               maxlength="3"
                               required
                               oninput="formatManageRegulationCode(this)"
                               value="{{ old('regcode') }}">
                    </div>
                    <div class="form-text small" style="font-size: 11px;">
                        Must be exactly 3 characters starting with R (e.g. <code>r22</code> &rarr; <code>R22</code>).
                    </div>
                </div>

                {{-- 3. Regulation Name (regname) --}}
                <div class="mb-3">
                    <label for="m_regname" class="form-label fw-bold small text-secondary">
                        Regulation Name (regname) <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="regname"
                           id="m_regname"
                           class="form-control form-control-sm"
                           placeholder="e.g. R22 MCA Academic Regulations & Course Scheme"
                           value="{{ old('regname') }}"
                           required>
                </div>

                {{-- 4. Description --}}
                <div class="mb-3">
                    <label for="m_description" class="form-label fw-bold small text-secondary">
                        Description
                    </label>
                    <textarea name="description"
                              id="m_description"
                              rows="2"
                              class="form-control form-control-sm"
                              placeholder="Brief description / summary...">{{ old('description') }}</textarea>
                </div>

                {{-- 5. File upload --}}
                <div class="mb-4">
                    <label for="m_document" class="form-label fw-bold small text-secondary">
                        Upload Regulation Document <span class="text-danger">*</span>
                    </label>
                    <input type="file"
                           name="document"
                           id="m_document"
                           class="form-control form-control-sm"
                           required>
                    <div class="form-text small" style="font-size: 11px;">
                        All document formats accepted (PDF, Word, Excel, PPT, ZIP, Images). Max 50 MB.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2" style="border-radius: 10px; font-weight: 700; height: 42px;">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                    <span>Upload &amp; Save to visa.ppr_details</span>
                </button>

            </form>
        </div>
    </div>

    {{-- Right Column: visa.ppr_details Table --}}
    <div class="col-lg-8">
        <div class="card p-4" style="border-radius: 16px; margin-top: 0; box-shadow: var(--card-shadow); border: 1px solid var(--border-card);">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h2 class="h5 fw-bold mb-0" style="color: var(--text-primary);">
                    <i class="bi bi-table text-primary me-2"></i>
                    visa.ppr_details Table ({{ $documents->total() }})
                </h2>
                <span class="badge bg-light text-secondary border">Schema: visa &bull; Table: ppr_details</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">S.no</th>
                            <th style="width: 75px;">Prog id</th>
                            <th style="width: 90px;">Prog name</th>
                            <th style="width: 90px;">regcode</th>
                            <th>regname</th>
                            <th>filename &amp; filepath</th>
                            <th>description</th>
                            <th class="text-end" style="width: 90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $item)
                            <tr>
                                {{-- S.no --}}
                                <td class="fw-bold text-muted">
                                    #{{ $item->s_no }}
                                </td>

                                {{-- Prog id --}}
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $item->prog_id ?? '-' }}</span>
                                </td>

                                {{-- Prog name --}}
                                <td>
                                    <span class="badge {{ $item->program_badge_class }}" style="font-weight: 700;">
                                        {{ $item->prog_name }}
                                    </span>
                                </td>

                                {{-- regcode --}}
                                <td>
                                    <span class="badge bg-warning text-dark fw-bold" style="letter-spacing: 0.5px;">
                                        {{ $item->regcode }}
                                    </span>
                                </td>

                                {{-- regname --}}
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->regname }}</div>
                                    <div class="small text-muted" style="font-size: 11px;">
                                        {{ $item->created_at ? $item->created_at->format('d M Y, h:i A') : 'Recent' }}
                                    </div>
                                </td>

                                {{-- filename & filepath --}}
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="{{ $item->file_icon }} me-1" style="font-size: 16px;"></i>
                                        <span class="fw-semibold text-truncate" style="max-width: 140px;" title="{{ $item->filename }}">
                                            {{ $item->filename }}
                                        </span>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 10.5px; max-width: 160px;" title="{{ $item->filepath }}">
                                        <code>{{ $item->filepath }}</code>
                                    </div>
                                </td>

                                {{-- description --}}
                                <td>
                                    <div class="text-muted text-truncate" style="max-width: 150px;" title="{{ $item->description }}">
                                        {{ $item->description ?: '-' }}
                                    </div>
                                </td>

                                {{-- Actions --}}
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('general.download', $item) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('general.destroy', $item) }}"
                                              class="d-inline m-0 p-0"
                                              onsubmit="return confirm('Delete regulation #{{ $item->s_no }} ({{ addslashes($item->regname) }})?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No records found in <code>visa.ppr_details</code>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $documents->links() }}
            </div>
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
</script>
@endpush
