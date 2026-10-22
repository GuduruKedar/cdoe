@extends('layouts.app')

@section('page_title', 'Document Upload')

@section('content')
<div class="cdoe-doc-upload-wrap">

    {{-- ================= 1. HEADER HERO BANNER ================= --}}
    <div class="dash-hero-card mb-4">
        <div class="dash-hero-content">
            <div class="dash-hero-badge">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Admissions • Institutional Document Repository
            </div>
            <h1 class="dash-hero-title">
                Student Document <span class="dash-hero-name">Upload</span>
            </h1>
            <p class="dash-hero-sub">
                Upload and manage applicant academic credentials and identity records. Photos must be JPG/PNG; all educational and identity certificates must be in PDF format. Preview selected files line by line before uploading.
            </p>
            <div class="dash-hero-actions">
                <a href="{{ route('admissions.docview') }}" class="dash-hero-btn outline">
                    <i class="bi bi-table"></i>
                    <span>Document View Matrix</span>
                </a>
                <a href="{{ route('admissions.datadownload') }}" class="dash-hero-btn outline">
                    <i class="bi bi-file-earmark-arrow-down"></i>
                    <span>Admission Data Download</span>
                </a>
            </div>
        </div>
        <div class="dash-hero-aside">
            <div class="dash-session-pill">
                <span class="dash-pulse-dot" style="background: #10b981;"></span>
                <span>Active Portal • Verified</span>
            </div>
        </div>
    </div>

    {{-- ================= 2. STUDENT SELECTION CARD ================= --}}
    <div class="card shadow-sm border-0 mb-4 cdoe-card">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="mb-0 fw-bold d-flex align-items-center" style="color: var(--text-primary);">
                    <i class="bi bi-person-badge-fill me-2 text-primary fs-5"></i> 1. Select Applicant / Student
                </h5>
                @if($isStudent)
                    <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill font-monospace">
                        <i class="bi bi-lock-fill me-1"></i> Student Mode (Logged-in User)
                    </span>
                @else
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                        <i class="bi bi-shield-check me-1"></i> Staff / Admin Verification Mode
                    </span>
                @endif
            </div>

            @if(!$isStudent)
                <div class="position-relative mb-2">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text cdoe-input-addon">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text"
                               id="studentSearchInput"
                               class="form-control cdoe-form-control"
                               placeholder="Type Register No, Application No, Student Name, or Email to search..."
                               autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary" id="clearStudentSearch" style="display: none;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    {{-- Autocomplete dropdown --}}
                    <div id="studentSearchResults" class="list-group position-absolute w-100 shadow-lg mt-1" style="display: none; z-index: 1050; max-height: 280px; overflow-y: auto;"></div>
                </div>

                {{-- Quick Select Student Pills --}}
                @if(isset($recentStudents) && $recentStudents->isNotEmpty())
                    <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
                        <span class="small text-muted fw-semibold d-inline-flex align-items-center">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Select:
                        </span>
                        @foreach($recentStudents as $rs)
                            <button type="button"
                                    class="btn btn-sm btn-quick-student {{ ($selectedStudent && $selectedStudent->application_no == $rs->application_no) ? 'active' : '' }}"
                                    onclick='selectStudent(@json($rs))'>
                                <span class="font-monospace fw-bold">{{ $rs->register_no }}</span>
                                <span class="quick-name ms-1">({{ \Illuminate\Support\Str::limit($rs->student_name, 15) }})</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            @endif

            {{-- Selected Student Summary Card --}}
            <div id="studentSummaryCard" class="p-3 rounded-3 mt-3 cdoe-summary-card" style="{{ $selectedStudent ? '' : 'display: none;' }}">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                             style="width: 54px; height: 54px; background: var(--theme-gradient, linear-gradient(135deg, #4f46e5, #06b6d4)); font-size: 20px;">
                            <span id="stdAvatarInitial">{{ strtoupper(substr($selectedStudent->student_name ?? 'U', 0, 1)) }}</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="mb-1 fw-bold" style="color: var(--text-primary);" id="stdName">
                                {{ $selectedStudent->student_name ?? '—' }}
                            </h6>
                            @if(!$isStudent)
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="clearSelectedStudent()" title="Clear selection">
                                    <i class="bi bi-x-circle me-1"></i> Switch Student
                                </button>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 text-muted small mt-1">
                            <span><strong>Reg No:</strong> <span class="font-monospace text-primary fw-bold" id="stdRegNo">{{ $selectedStudent->register_no ?? '—' }}</span></span>
                            <span>•</span>
                            <span><strong>App No:</strong> <span class="font-monospace" id="stdAppNo">{{ $selectedStudent->application_no ?? '—' }}</span></span>
                            <span>•</span>
                            <span><strong>Program:</strong> <span id="stdProgram">{{ $selectedStudent->program_code ?? '—' }}</span></span>
                            <span>•</span>
                            <span><strong>Batch:</strong> <span id="stdBatch">{{ $selectedStudent->adm_batch_code ?? '—' }}</span></span>
                            <span>•</span>
                            <span><strong>Email:</strong> <span id="stdEmail">{{ $selectedStudent->personal_email ?? '—' }}</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="noStudentAlert" class="alert alert-warning mb-0 mt-3 d-flex align-items-center gap-2" style="{{ $selectedStudent ? 'display: none !important;' : '' }}">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span>Please select a student above or click one of the quick select pills to upload and manage their documents.</span>
            </div>
        </div>
    </div>

    {{-- ================= 3. DOCUMENT UPLOAD & LINE-BY-LINE STAGING ================= --}}
    <div class="card shadow-sm border-0 mb-4 cdoe-card" id="uploadSectionCard">
        <div class="card-body p-4">
            <h5 class="mb-3 fw-bold d-flex align-items-center" style="color: var(--text-primary);">
                <i class="bi bi-file-earmark-arrow-up-fill me-2 text-primary fs-5"></i> 2. Select Document Type & File
            </h5>

            {{-- File Selection Form Controls --}}
            <div class="row g-3 align-items-end mb-4">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fw-semibold small text-muted">DOCUMENT TYPE</label>
                    <select id="docTypeSelect" class="form-select form-select-lg cdoe-select">
                        <option value="">-- Choose Document Type --</option>
                        <optgroup label="Core Academic & ID (Photo = JPG, Rest = PDF)">
                            <option value="photo" data-ext="jpg,jpeg,png" data-isimage="1">Passport Photo (JPG / JPEG / PNG)</option>
                            <option value="10th" data-ext="pdf" data-isimage="0">10th / SSC Certificate (PDF)</option>
                            <option value="inter" data-ext="pdf" data-isimage="0">Intermediate / +2 Certificate (PDF)</option>
                            <option value="ug" data-ext="pdf" data-isimage="0">UG Degree / Marks Memo (PDF)</option>
                            <option value="ug1" data-ext="pdf" data-isimage="0">UG Provisional / CMM - UG1 (PDF)</option>
                            <option value="pg" data-ext="pdf" data-isimage="0">PG Degree / Marks Memo (PDF)</option>
                            <option value="pg1" data-ext="pdf" data-isimage="0">PG Provisional / CMM - PG1 (PDF)</option>
                        </optgroup>
                        <optgroup label="Government & Identification">
                            <option value="aadhar" data-ext="pdf" data-isimage="0">Aadhar Card (PDF)</option>
                            <option value="pan" data-ext="pdf" data-isimage="0">PAN Card (PDF)</option>
                            <option value="apaar" data-ext="pdf" data-isimage="0">APAAR ID (PDF)</option>
                            <option value="diploma" data-ext="pdf" data-isimage="0">Diploma Certificate (PDF)</option>
                            <option value="phd" data-ext="pdf" data-isimage="0">PhD / Doctorate (PDF)</option>
                            <option value="other" data-ext="pdf" data-isimage="0">Other Certificate (PDF)</option>
                        </optgroup>
                    </select>
                </div>

                <div class="col-lg-6 col-md-6">
                    <label class="form-label fw-semibold small text-muted">CHOOSE FILE</label>
                    <input type="file"
                           id="fileInput"
                           class="form-control form-control-lg cdoe-form-control"
                           accept=".jpg,.jpeg,.png,.pdf">
                    <div class="form-text small" id="fileHelpText">
                        <i class="bi bi-info-circle me-1"></i> Select a document type first. Photos accept JPG/PNG; all other documents require PDF.
                    </div>
                </div>

                <div class="col-lg-2 col-md-12">
                    <button type="button" class="btn btn-primary btn-lg w-100" id="addToFileQueueBtn" style="background: var(--theme-gradient); border-color: var(--theme-primary);">
                        <i class="bi bi-plus-circle me-1"></i> Add to Queue
                    </button>
                </div>
            </div>

            {{-- Staged Files Queue (Line by line preview) --}}
            <div class="border rounded-3 p-3 mb-3 queue-container">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold" style="color: var(--text-primary); font-size: 14px;">
                        <i class="bi bi-list-check me-1 text-primary"></i> Preview Selected Files (Line by Line)
                    </span>
                    <span class="badge bg-secondary rounded-pill" id="queueCountBadge">0 files staged</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 cdoe-table" id="queueTable">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 15%;">Reg No</th>
                                <th style="width: 20%;">Doc Type</th>
                                <th style="width: 25%;">File Name</th>
                                <th style="width: 15%;">Preview</th>
                                <th style="width: 10%;">Size</th>
                                <th style="width: 10%; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="queueTableBody">
                            <tr id="emptyQueueRow">
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-cloud-arrow-up display-6 d-block mb-2 text-muted"></i>
                                    <span>No files staged yet. Select document type and file above, then click <strong>Add to Queue</strong>.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Action Button Bar --}}
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2" id="queueActionBar" style="display: none !important;">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="clearQueueBtn">
                        <i class="bi bi-trash3 me-1"></i> Clear Queue
                    </button>
                    <button type="button" class="btn btn-success btn-lg px-4" id="uploadAllQueueBtn" style="font-weight: 600;">
                        <i class="bi bi-cloud-upload-fill me-2"></i> Upload All Selected (<span id="queueUploadCount">0</span>)
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= 4. EXISTING UPLOADED DOCUMENTS TABLE ================= --}}
    <div class="card shadow-sm border-0 cdoe-card" id="existingDocsCard" style="{{ $selectedStudent ? '' : 'display: none;' }}">
        <div class="card-header border-0 bg-transparent py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">
                    <i class="bi bi-check-circle-fill text-success me-2"></i> Uploaded Documents for Selected Student
                </h5>
                <p class="text-muted small mb-0">Live verified documents stored in institutional repository</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="refreshDocsBtn">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 cdoe-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Doc ID</th>
                        <th style="width: 14%;">Doc No</th>
                        <th style="width: 14%;">Doc Type</th>
                        <th style="width: 26%;">File Name</th>
                        <th style="width: 16%;">Uploaded At</th>
                        <th style="width: 10%;">Preview</th>
                        <th style="width: 12%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="existingDocsTableBody">
                    @forelse($uploadedDocuments as $doc)
                        @php
                            $isImg = $doc->is_image;
                            $fileUrl = $doc->file_url;
                        @endphp
                        <tr id="docRow_{{ $doc->doc_id }}">
                            <td class="font-monospace text-muted">{{ $doc->doc_id }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $doc->docno ?: 'DOC-' . $doc->doc_id }}</span></td>
                            <td>
                                <span class="badge {{ $isImg ? 'bg-primary' : 'bg-info text-dark' }} text-uppercase">
                                    {{ $doc->doctype }}
                                </span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 250px;" title="{{ $doc->docname }}">
                                    <i class="bi {{ $isImg ? 'bi-image' : 'bi-file-earmark-pdf-fill text-danger' }} me-1"></i>
                                    {{ $doc->docname }}
                                </div>
                            </td>
                            <td class="small text-muted">{{ $doc->log_timestamp ? \Carbon\Carbon::parse($doc->log_timestamp)->format('d M Y, h:i A') : '—' }}</td>
                            <td>
                                @if($isImg)
                                    <img src="{{ $fileUrl }}"
                                         class="rounded shadow-sm"
                                         style="width: 40px; height: 40px; object-fit: cover; cursor: pointer; border: 2px solid #0d6efd;"
                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($doc->docname) }}&background=0d6efd&color=fff&size=64';"
                                         onclick="openPreviewModal('{{ $fileUrl }}', '{{ addslashes($doc->docname) }}', true)"
                                         alt="Preview">
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="openPreviewModal('{{ $fileUrl }}', '{{ addslashes($doc->docname) }}', false)">
                                        <i class="bi bi-eye"></i> View PDF
                                    </button>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ $fileUrl }}" download="{{ $doc->docname }}" class="btn btn-sm btn-outline-primary me-1" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDocument({{ $doc->doc_id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="noExistingDocsRow">
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-folder2-open display-6 d-block mb-2 text-muted"></i>
                                No documents have been uploaded for this student yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ================= 5. INTERACTIVE PREVIEW MODAL ================= --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg cdoe-modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="previewModalTitle">Document Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3" id="previewModalBody">
                {{-- Dynamic injection: Image or Iframe PDF --}}
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="#" id="previewDownloadBtn" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i> Download File
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // State variables
    let currentAppNo = {{ $selectedStudent->application_no ?? 'null' }};
    let currentRegNo = '{{ $selectedStudent->register_no ?? '' }}';
    let fileQueue = [];

    // Document types definition
    const docTypesConfig = {
        'photo': { label: 'Passport Photo', ext: ['jpg','jpeg','png','webp'], isImage: true },
        '10th': { label: '10th / SSC Certificate', ext: ['pdf'], isImage: false },
        'inter': { label: 'Intermediate / +2 Certificate', ext: ['pdf'], isImage: false },
        'ug': { label: 'UG Degree / Marks Memo', ext: ['pdf'], isImage: false },
        'ug1': { label: 'UG Provisional / CMM (UG1)', ext: ['pdf'], isImage: false },
        'pg': { label: 'PG Degree / Marks Memo', ext: ['pdf'], isImage: false },
        'pg1': { label: 'PG Provisional / CMM (PG1)', ext: ['pdf'], isImage: false },
        'aadhar': { label: 'Aadhar Card', ext: ['pdf'], isImage: false },
        'pan': { label: 'PAN Card', ext: ['pdf'], isImage: false },
        'apaar': { label: 'APAAR ID Card', ext: ['pdf'], isImage: false },
        'diploma': { label: 'Diploma Certificate', ext: ['pdf'], isImage: false },
        'phd': { label: 'PhD / Doctorate', ext: ['pdf'], isImage: false },
        'other': { label: 'Other Certificate', ext: ['pdf'], isImage: false },
    };

    // Auto-update file input accept attribute based on selected doctype
    document.getElementById('docTypeSelect').addEventListener('change', function() {
        const val = this.value;
        const fileInput = document.getElementById('fileInput');
        const helpText = document.getElementById('fileHelpText');

        if (val === 'photo') {
            fileInput.setAttribute('accept', '.jpg,.jpeg,.png,.webp');
            helpText.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Passport Photo selected: Acceptable formats are JPG, JPEG, PNG</span>';
        } else if (val) {
            fileInput.setAttribute('accept', '.pdf');
            helpText.innerHTML = '<span class="text-primary fw-semibold"><i class="bi bi-file-earmark-pdf-fill me-1"></i> ' + (docTypesConfig[val]?.label || val) + ' selected: Acceptable format is strictly PDF</span>';
        } else {
            fileInput.setAttribute('accept', '.jpg,.jpeg,.png,.pdf');
            helpText.innerHTML = '<span class="text-muted"><i class="bi bi-info-circle me-1"></i> Select a document type first. Photos accept JPG/PNG; all other documents require PDF.</span>';
        }
    });

    // Student Search Live Autocomplete (for staff/admin)
    const searchInput = document.getElementById('studentSearchInput');
    const searchResults = document.getElementById('studentSearchResults');
    const clearBtn = document.getElementById('clearStudentSearch');

    if (searchInput) {
        let debounceTimer = null;
        searchInput.addEventListener('input', function() {
            const q = this.value.trim();
            if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';

            clearTimeout(debounceTimer);
            if (q.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`{{ route('admissions.docupload.lookup') }}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (!data.students || data.students.length === 0) {
                            searchResults.innerHTML = '<div class="list-group-item text-muted">No students matching query.</div>';
                            searchResults.style.display = 'block';
                            return;
                        }

                        data.students.forEach(s => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                            item.innerHTML = `
                                <div>
                                    <div class="fw-bold text-primary">${s.student_name}</div>
                                    <small class="text-muted">Reg: <strong class="font-monospace">${s.register_no}</strong> | App: ${s.application_no} | ${s.program_code || ''}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">Select</span>
                            `;
                            item.addEventListener('click', (e) => {
                                e.preventDefault();
                                selectStudent(s);
                                searchResults.style.display = 'none';
                                searchInput.value = '';
                                if (clearBtn) clearBtn.style.display = 'none';
                            });
                            searchResults.appendChild(item);
                        });
                        searchResults.style.display = 'block';
                    });
            }, 250);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                clearBtn.style.display = 'none';
                searchResults.style.display = 'none';
            });
        }

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }

    function selectStudent(student) {
        currentAppNo = student.application_no;
        currentRegNo = student.register_no;

        document.getElementById('stdName').textContent = student.student_name;
        document.getElementById('stdRegNo').textContent = student.register_no;
        document.getElementById('stdAppNo').textContent = student.application_no;
        document.getElementById('stdProgram').textContent = student.program_code || '—';
        document.getElementById('stdBatch').textContent = student.adm_batch_code || '—';
        document.getElementById('stdEmail').textContent = student.personal_email || '—';
        document.getElementById('stdAvatarInitial').textContent = (student.student_name || 'U').charAt(0).toUpperCase();

        document.getElementById('studentSummaryCard').style.display = 'block';
        const noStdAlert = document.getElementById('noStudentAlert');
        if (noStdAlert) noStdAlert.style.setProperty('display', 'none', 'important');

        // Update active quick pill
        document.querySelectorAll('.btn-quick-student').forEach(btn => {
            if (btn.textContent.includes(student.register_no)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        document.getElementById('existingDocsCard').style.display = 'block';

        loadStudentDocuments(currentAppNo);
    }

    function clearSelectedStudent() {
        currentAppNo = null;
        currentRegNo = '';
        document.getElementById('studentSummaryCard').style.display = 'none';
        const noStdAlert = document.getElementById('noStudentAlert');
        if (noStdAlert) noStdAlert.style.removeProperty('display');
        document.getElementById('existingDocsCard').style.display = 'none';
        document.querySelectorAll('.btn-quick-student').forEach(btn => btn.classList.remove('active'));
    }

    function loadStudentDocuments(appNo) {
        fetch(`{{ route('admissions.docupload.documents') }}?application_no=${appNo}`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('existingDocsTableBody');
                tbody.innerHTML = '';
                if (!data.documents || data.documents.length === 0) {
                    tbody.innerHTML = `
                        <tr id="noExistingDocsRow">
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-folder2-open display-6 d-block mb-2 text-muted"></i>
                                No documents have been uploaded for this student yet.
                            </td>
                        </tr>
                    `;
                    return;
                }

                data.documents.forEach(doc => {
                    const tr = document.createElement('tr');
                    tr.id = `docRow_${doc.doc_id}`;
                    tr.innerHTML = `
                        <td class="font-monospace text-muted">${doc.doc_id}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary font-monospace">${doc.docno || 'DOC-' + doc.doc_id}</span></td>
                        <td><span class="badge ${doc.is_image ? 'bg-primary' : 'bg-info text-dark'} text-uppercase">${doc.doctype}</span></td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;" title="${doc.docname}">
                                <i class="bi ${doc.is_image ? 'bi-image' : 'bi-file-earmark-pdf-fill text-danger'} me-1"></i>
                                ${doc.docname}
                            </div>
                        </td>
                        <td class="small text-muted">${doc.log_timestamp}</td>
                        <td>
                            ${doc.is_image
                                ? `<img src="${doc.file_url}" class="rounded shadow-sm" style="width: 40px; height: 40px; object-fit: cover; cursor: pointer; border: 2px solid #0d6efd;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(doc.docname)}&background=0d6efd&color=fff&size=64';" onclick="openPreviewModal('${doc.file_url}', '${doc.docname.replace(/'/g, "\\'")}', true)" alt="Preview">`
                                : `<button type="button" class="btn btn-sm btn-outline-danger" onclick="openPreviewModal('${doc.file_url}', '${doc.docname.replace(/'/g, "\\'")}', false)"><i class="bi bi-eye"></i> View PDF</button>`
                            }
                        </td>
                        <td class="text-center">
                            <a href="${doc.file_url}" download="${doc.docname}" class="btn btn-sm btn-outline-primary me-1" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDocument(${doc.doc_id})" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    // Add to Queue (Line by line staging)
    document.getElementById('addToFileQueueBtn').addEventListener('click', function() {
        if (!currentAppNo) {
            alert('Please select a student first from the top section.');
            document.getElementById('studentSearchInput')?.focus();
            return;
        }

        const docTypeSelect = document.getElementById('docTypeSelect');
        const docType = docTypeSelect.value;
        const fileInput = document.getElementById('fileInput');

        if (!docType) {
            alert('Please choose a document type from the dropdown.');
            docTypeSelect.focus();
            return;
        }

        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Please browse and select a file.');
            fileInput.focus();
            return;
        }

        const file = fileInput.files[0];
        const ext = file.name.split('.').pop().toLowerCase();

        // Validation rule: photo must be jpg/jpeg/png/webp; else all pdf
        if (docType === 'photo' && !['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
            alert('Passport Photo must be in JPG, JPEG, or PNG format.');
            return;
        } else if (docType !== 'photo' && ext !== 'pdf') {
            alert(`Document '${docTypesConfig[docType]?.label || docType}' must be in PDF format.`);
            return;
        }

        // Add to queue array
        const queueItem = {
            id: 'queue_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            application_no: currentAppNo,
            register_no: currentRegNo,
            doctype: docType,
            doctypename: docTypesConfig[docType]?.label || docType,
            file: file,
            filename: file.name,
            filesize: (file.size / 1024).toFixed(1) + ' KB',
            isImage: docType === 'photo',
            objectUrl: docType === 'photo' ? URL.createObjectURL(file) : null
        };

        fileQueue.push(queueItem);
        renderQueue();

        // Reset file input
        fileInput.value = '';
    });

    function renderQueue() {
        const tbody = document.getElementById('queueTableBody');
        const badge = document.getElementById('queueCountBadge');
        const countSpan = document.getElementById('queueUploadCount');
        const actionBar = document.getElementById('queueActionBar');

        if (fileQueue.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyQueueRow">
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-cloud-arrow-up display-6 d-block mb-2 text-muted"></i>
                        <span>No files staged yet. Select document type and file above, then click <strong>Add to Queue</strong>.</span>
                    </td>
                </tr>
            `;
            badge.textContent = '0 files staged';
            badge.className = 'badge bg-secondary rounded-pill';
            actionBar.style.setProperty('display', 'none', 'important');
            return;
        }

        badge.textContent = `${fileQueue.length} files staged`;
        badge.className = 'badge bg-primary rounded-pill';
        countSpan.textContent = fileQueue.length;
        actionBar.style.setProperty('display', 'flex', 'important');

        tbody.innerHTML = '';
        fileQueue.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.id = item.id;
            tr.innerHTML = `
                <td class="fw-bold text-muted">${idx + 1}</td>
                <td><span class="badge bg-secondary-subtle text-secondary font-monospace">${item.register_no}</span></td>
                <td><span class="badge ${item.isImage ? 'bg-primary' : 'bg-info text-dark'}">${item.doctypename}</span></td>
                <td>
                    <div class="text-truncate" style="max-width: 220px;" title="${item.filename}">
                        <i class="bi ${item.isImage ? 'bi-image' : 'bi-file-earmark-pdf-fill text-danger'} me-1"></i>
                        ${item.filename}
                    </div>
                </td>
                <td>
                    ${item.isImage
                        ? `<img src="${item.objectUrl}" class="rounded shadow-sm" style="width: 40px; height: 40px; object-fit: cover; cursor: pointer; border: 2px solid #0d6efd;" onclick="openPreviewModal('${item.objectUrl}', '${item.filename.replace(/'/g, "\\'")}', true)" alt="Preview">`
                        : `<span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-file-earmark-pdf me-1"></i>PDF Ready</span>`
                    }
                </td>
                <td class="small text-muted font-monospace">${item.filesize}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromQueue('${item.id}')" title="Remove line">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function removeFromQueue(id) {
        fileQueue = fileQueue.filter(item => item.id !== id);
        renderQueue();
    }

    document.getElementById('clearQueueBtn').addEventListener('click', function() {
        if (confirm('Clear all staged files?')) {
            fileQueue = [];
            renderQueue();
        }
    });

    // Upload All Queued Files
    document.getElementById('uploadAllQueueBtn').addEventListener('click', async function() {
        if (fileQueue.length === 0) return;

        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Uploading line by line...';

        let successCount = 0;
        let failCount = 0;

        for (let i = 0; i < fileQueue.length; i++) {
            const item = fileQueue[i];
            const formData = new FormData();
            formData.append('application_no', item.application_no);
            formData.append('register_no', item.register_no);
            formData.append('doctype', item.doctype);
            formData.append('file', item.file);

            try {
                const response = await fetch("{{ route('admissions.docupload.upload') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                const resData = await response.json();
                if (resData.success) {
                    successCount++;
                } else {
                    failCount++;
                    alert(`Error uploading ${item.filename}: ${resData.message}`);
                }
            } catch (err) {
                failCount++;
                console.error(err);
            }
        }

        btn.disabled = false;
        btn.innerHTML = originalHtml;

        fileQueue = [];
        renderQueue();

        if (currentAppNo) {
            loadStudentDocuments(currentAppNo);
        }

        alert(`Upload complete! Successfully uploaded ${successCount} document(s).` + (failCount > 0 ? ` (${failCount} failed)` : ''));
    });

    // Delete existing document
    window.deleteDocument = function(docId) {
        if (!confirm('Are you sure you want to delete this uploaded document?')) return;

        fetch(`{{ url('admissions/docupload') }}/${docId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`docRow_${docId}`);
                if (row) row.remove();
                alert('Document deleted successfully.');
            } else {
                alert('Delete failed: ' + data.message);
            }
        });
    };

    // Modal preview
    window.openPreviewModal = function(url, title, isImage) {
        document.getElementById('previewModalTitle').textContent = title || 'Document Preview';
        document.getElementById('previewDownloadBtn').href = url;
        const body = document.getElementById('previewModalBody');

        if (isImage) {
            body.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center p-2">
                    <img src="${url}" class="img-fluid rounded shadow-sm" style="max-height: 65vh; max-width: 100%; object-fit: contain;" alt="Preview"
                         onerror="this.parentElement.innerHTML='<div class=\\'alert alert-warning py-4 px-3 mb-0 text-center\\'><i class=\\'bi bi-image fs-1 text-warning d-block mb-2\\'></i><strong>Image Preview Not Available</strong><p class=\\'text-muted small mb-3\\'>The image file could not be rendered or was not found in storage.</p><a href=\\'${url}\\' class=\\'btn btn-sm btn-outline-primary\\' target=\\'_blank\\'><i class=\\'bi bi-box-arrow-up-right me-1\\'></i>Open Direct Link</a></div>';">
                </div>
            `;
        } else {
            body.innerHTML = `
                <div style="height: 70vh; width: 100%;">
                    <iframe src="${url}" style="width: 100%; height: 100%; border: none; border-radius: 8px;"></iframe>
                </div>
            `;
        }

        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    };

    document.getElementById('refreshDocsBtn')?.addEventListener('click', () => {
        if (currentAppNo) loadStudentDocuments(currentAppNo);
    });
</script>

<style>
    .cdoe-doc-upload-wrap {
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Cards adhering to global dark/light theme */
    .cdoe-card {
        border-radius: 14px;
        background-color: var(--bg-card) !important;
        border: 1px solid var(--border-card) !important;
        box-shadow: var(--card-shadow, 0 4px 18px rgba(0, 0, 0, 0.08)) !important;
        color: var(--text-primary);
        backdrop-filter: blur(12px);
    }

    .cdoe-summary-card {
        background-color: var(--bg-surface) !important;
        border: 1px solid var(--border-subtle) !important;
    }

    .queue-container {
        background-color: var(--bg-surface) !important;
        border: 1px solid var(--border-subtle) !important;
    }

    /* Input controls adhering to dark/light theme */
    .cdoe-form-control, .cdoe-select {
        background-color: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-subtle) !important;
    }
    .cdoe-form-control:focus, .cdoe-select:focus {
        background-color: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2) !important;
    }
    .cdoe-form-control::placeholder {
        color: var(--text-muted) !important;
        opacity: 0.8;
    }
    .cdoe-input-addon {
        background-color: var(--bg-surface) !important;
        color: var(--text-muted) !important;
        border: 1px solid var(--border-subtle) !important;
    }

    /* Quick select student pills */
    .btn-quick-student {
        background-color: var(--bg-surface);
        color: var(--text-primary);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        font-size: 11.5px;
        padding: 4px 12px;
        transition: all 0.15s ease;
    }
    .btn-quick-student:hover {
        background-color: var(--bg-elevated, #1a2844);
        border-color: #0d6efd;
        color: #0d6efd;
        transform: translateY(-1px);
    }
    .btn-quick-student.active {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        border-color: #0d6efd !important;
    }
    .btn-quick-student.active .quick-name {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    .quick-name {
        color: var(--text-muted);
    }

    /* Autocomplete list */
    #studentSearchResults {
        background: var(--bg-card);
        border: 1px solid var(--border-card);
    }
    #studentSearchResults .list-group-item {
        background-color: var(--bg-surface) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-subtle) !important;
    }
    #studentSearchResults .list-group-item:hover {
        background-color: var(--bg-hover-row) !important;
    }

    /* Tables */
    .cdoe-table {
        color: var(--text-primary);
        border-color: var(--border-subtle) !important;
    }
    .cdoe-table thead th {
        background-color: #e83e8c !important;
        color: #ffffff !important;
        border-color: #d63384 !important;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
        white-space: nowrap;
    }
    .cdoe-table tbody td {
        background-color: transparent !important;
        color: var(--text-primary) !important;
        border-color: var(--border-subtle) !important;
        vertical-align: middle;
        padding: 10px 12px;
        font-size: 13px;
    }
    .cdoe-table tbody tr:hover td {
        background-color: var(--bg-hover-row) !important;
    }

    /* Modal styling */
    .cdoe-modal-content {
        background-color: var(--bg-card) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-card) !important;
        border-radius: 16px;
    }

    /* Hero Banner */
    .dash-hero-card {
        background: var(--theme-gradient, linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%));
        border-radius: 16px;
        padding: 28px 32px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        box-shadow: 0 10px 25px -5px rgba(49, 46, 129, 0.25);
    }
    .dash-hero-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 600;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .dash-hero-title {
        font-size: 26px;
        font-weight: 800;
        margin: 0 0 8px 0;
        letter-spacing: -0.5px;
    }
    .dash-hero-name {
        background: linear-gradient(135deg, #38bdf8, #818cf8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .dash-hero-sub {
        font-size: 13.5px;
        color: rgba(255, 255, 255, 0.82);
        margin: 0 0 18px 0;
        max-width: 720px;
        line-height: 1.5;
    }
    .dash-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .dash-hero-btn.outline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 9999px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .dash-hero-btn.outline:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
    }
    .dash-session-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }
    .dash-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulseDot 2s infinite;
    }
    @keyframes pulseDot {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>
@endsection
