@extends('layouts.app')

@section('page_title', 'Manage Regulations & Documents')

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

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold text-primary mb-1">
            <i class="bi bi-gear-wide-connected me-2"></i>
            Manage Regulations &amp; Documents
        </h1>
        <p class="text-muted small mb-0">Main Admin portal for uploading, editing, and managing dynamic regulation sections for Online MBA &amp; BBA.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('general.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 600;">
            <i class="bi bi-grid"></i>
            <span>Back to Cards View</span>
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- Left Column: Upload New Regulation Form --}}
    <div class="col-lg-5">
        <div class="card p-4" style="border-radius: 16px; margin-top: 0;">
            <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2" style="color: var(--text-primary);">
                <i class="bi bi-cloud-arrow-up-fill text-primary"></i>
                Upload New Document / Regulation
            </h2>

            <form method="POST" action="{{ route('general.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Program Selection --}}
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">
                        Program <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="program" id="pMBA" value="MBA" checked>
                            <label class="form-check-label fw-semibold small" for="pMBA">Online MBA</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="program" id="pBBA" value="BBA">
                            <label class="form-check-label fw-semibold small" for="pBBA">Online BBA</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="program" id="pCommon" value="COMMON">
                            <label class="form-check-label fw-semibold small" for="pCommon">Common</label>
                        </div>
                    </div>
                </div>

                {{-- Subcategory Dropdown --}}
                <div class="mb-3">
                    <label for="m_sub_category" class="form-label fw-bold small text-secondary">
                        Sub-Category / Nav Section <span class="text-danger">*</span>
                    </label>
                    <select name="sub_category" id="m_sub_category" class="form-select form-select-sm" onchange="toggleManageNewCat(this.value)">
                        <option value="Regulations">Regulations</option>
                        <option value="Syllabus">Syllabus</option>
                        <option value="Exam Guidelines">Exam Guidelines</option>
                        <option value="Academic Calendar">Academic Calendar</option>
                        <option value="Curriculum">Curriculum</option>
                        @foreach ($existingSubCategories as $cat)
                            @if (!in_array($cat, ['Regulations', 'Syllabus', 'Exam Guidelines', 'Academic Calendar', 'Curriculum']))
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endif
                        @endforeach
                        <option value="__NEW__">+ Create New Sub-Category...</option>
                    </select>
                </div>

                {{-- New Subcategory input --}}
                <div class="mb-3" id="m_newCatWrap" style="display: none;">
                    <label for="m_new_sub_category" class="form-label fw-bold small text-secondary">
                        New Sub-Category Name
                    </label>
                    <input type="text"
                           name="new_sub_category"
                           id="m_new_sub_category"
                           class="form-control form-control-sm"
                           placeholder="e.g. Capstone Projects, Grading Norms">
                </div>

                {{-- Regulation Code --}}
                <div class="mb-3">
                    <label for="m_regulation_code" class="form-label fw-bold small text-secondary">
                        Regulation Code (Optional)
                    </label>
                    <input type="text"
                           name="regulation_code"
                           id="m_regulation_code"
                           class="form-control form-control-sm"
                           placeholder="e.g. R22, R21, R24">
                </div>

                {{-- Title --}}
                <div class="mb-3">
                    <label for="m_title" class="form-label fw-bold small text-secondary">
                        Document Title <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="title"
                           id="m_title"
                           class="form-control form-control-sm"
                           placeholder="e.g. R22 MBA Academic Regulations"
                           required>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label for="m_description" class="form-label fw-bold small text-secondary">
                        Description / Summary
                    </label>
                    <textarea name="description"
                              id="m_description"
                              rows="2"
                              class="form-control form-control-sm"
                              placeholder="Brief description..."></textarea>
                </div>

                {{-- File upload --}}
                <div class="mb-4">
                    <label for="m_document" class="form-label fw-bold small text-secondary">
                        Upload Document File <span class="text-danger">*</span>
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
                    <span>Upload &amp; Publish Document</span>
                </button>

            </form>
        </div>
    </div>

    {{-- Right Column: Uploaded Documents Table --}}
    <div class="col-lg-7">
        <div class="card p-4" style="border-radius: 16px; margin-top: 0;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 fw-bold mb-0" style="color: var(--text-primary);">
                    <i class="bi bi-file-earmark-check-fill text-success me-2"></i>
                    Published Documents ({{ $documents->total() }})
                </h2>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Program</th>
                            <th>Title &amp; Sub-Category</th>
                            <th>File</th>
                            <th>Downloads</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $item)
                            <tr>
                                <td>
                                    <span class="badge {{ $item->program === 'MBA' ? 'bg-primary' : ($item->program === 'BBA' ? 'bg-purple' : 'bg-secondary') }}" style="font-weight: 700;">
                                        {{ $item->program }}
                                    </span>
                                    @if ($item->regulation_code)
                                        <div class="small text-muted mt-1 fw-bold">{{ $item->regulation_code }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->title }}</div>
                                    <div class="small text-muted">
                                        <span class="badge bg-light text-dark border">{{ $item->sub_category }}</span>
                                        • {{ $item->created_at ? $item->created_at->format('d M Y') : 'Recent' }}
                                    </div>
                                </td>
                                <td>
                                    <i class="{{ $item->file_icon }} me-1" style="font-size: 18px;"></i>
                                    <span class="fw-semibold text-uppercase" style="font-size: 11px;">{{ $item->file_type }}</span>
                                    <div class="small text-muted">{{ $item->formatted_size }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <i class="bi bi-download me-1"></i>{{ $item->downloads_count }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('general.download', $item) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('general.destroy', $item) }}"
                                              class="d-inline m-0 p-0"
                                              onsubmit="return confirm('Delete document: {{ addslashes($item->title) }}?')">
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
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No documents published yet.
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
    function toggleManageNewCat(val) {
        const wrap = document.getElementById('m_newCatWrap');
        const input = document.getElementById('m_new_sub_category');
        if (val === '__NEW__') {
            wrap.style.display = 'block';
            input.required = true;
            input.focus();
        } else {
            wrap.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }
</script>
@endpush
