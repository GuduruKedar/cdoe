@extends('layouts.app')

@section('page_title', 'Create Employee')

@section('content')
<div class="container-fluid px-0">

    {{-- Top Action Bar / Header --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <a href="{{ route('hrms.empsearch') }}" class="ss-back-btn">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Employee Search</span>
            </a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill">
                <i class="bi bi-shield-lock me-1"></i> HRMS Administration
            </span>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <div class="d-flex align-items-center gap-2 fw-bold mb-1">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="ss-card">
        <div class="ss-card-header">
            <div class="d-flex align-items-center gap-3">
                <div class="ss-header-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold" style="color: var(--text-primary); font-size: 17px;">New Employee Onboarding</h5>
                    <p class="mb-0 small" style="color: var(--text-muted);">Fill in employee information to register in HRMS and provision portal access.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('hrms.empcreation.store') }}" method="POST" id="empCreationForm">
            @csrf

            <div class="ss-card-body p-4">

                {{-- SECTION 1: Personal & Identity --}}
                <div class="ss-form-section mb-4">
                    <div class="ss-section-title">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        <span>1. Personal &amp; Employment Details</span>
                    </div>

                    <div class="row g-3">
                        {{-- Employee Code --}}
                        <div class="col-md-4">
                            <label for="emp_code" class="form-label ss-label required">Employee Code</label>
                            <div class="input-group">
                                <span class="input-group-text ss-ig-text"><i class="bi bi-hash"></i></span>
                                <input type="text"
                                       name="emp_code"
                                       id="emp_code"
                                       class="form-control ss-input @error('emp_code') is-invalid @enderror"
                                       placeholder="e.g. 02266 or EMP1001"
                                       value="{{ old('emp_code') }}"
                                       required
                                       autocomplete="off">
                            </div>
                            @error('emp_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text ss-help">Unique institutional employee identifier.</div>
                        </div>

                        {{-- Full Name --}}
                        <div class="col-md-5">
                            <label for="full_name" class="form-label ss-label required">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text ss-ig-text"><i class="bi bi-person"></i></span>
                                <input type="text"
                                       name="full_name"
                                       id="full_name"
                                       class="form-control ss-input @error('full_name') is-invalid @enderror"
                                       placeholder="e.g. Dr. Ramesh Kumar"
                                       value="{{ old('full_name') }}"
                                       required>
                            </div>
                            @error('full_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Employment Type --}}
                        <div class="col-md-3">
                            <label for="emp_type" class="form-label ss-label required">Employment Type</label>
                            <select name="emp_type"
                                    id="emp_type"
                                    class="form-select ss-select @error('emp_type') is-invalid @enderror"
                                    required>
                                <option value="" disabled {{ old('emp_type') ? '' : 'selected' }}>Select Type</option>
                                @foreach ($empTypes as $type)
                                    <option value="{{ $type }}" {{ old('emp_type', 'PERMANENT') === $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('emp_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date of Joining --}}
                        <div class="col-md-4">
                            <label for="date_of_join" class="form-label ss-label">Date of Joining</label>
                            <div class="input-group">
                                <span class="input-group-text ss-ig-text"><i class="bi bi-calendar3"></i></span>
                                <input type="date"
                                       name="date_of_join"
                                       id="date_of_join"
                                       class="form-control ss-input @error('date_of_join') is-invalid @enderror"
                                       value="{{ old('date_of_join', date('Y-m-d')) }}">
                            </div>
                            @error('date_of_join')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Active Status Toggle --}}
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check form-switch ss-switch mt-3">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active" style="color: var(--text-primary); cursor: pointer;">
                                    Active Employee Status
                                </label>
                                <div class="small" style="color: var(--text-muted); font-size: 11.5px;">Allows system access and active payroll/duty mapping.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Contact & Communications --}}
                <div class="ss-form-section mb-4">
                    <div class="ss-section-title">
                        <i class="bi bi-envelope-at text-success me-2"></i>
                        <span>2. Contact &amp; Communication Details</span>
                    </div>

                    <div class="row g-3">
                        {{-- Official Email --}}
                        <div class="col-md-6">
                            <label for="ofc_mailid" class="form-label ss-label required">Official Email</label>
                            <div class="input-group">
                                <span class="input-group-text ss-ig-text"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email"
                                       name="ofc_mailid"
                                       id="ofc_mailid"
                                       class="form-control ss-input @error('ofc_mailid') is-invalid @enderror"
                                       placeholder="e.g. 02266@vignan.ac.in"
                                       value="{{ old('ofc_mailid') }}"
                                       required>
                            </div>
                            @error('ofc_mailid')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text ss-help">Official institutional email used for portal notifications and credentials.</div>
                        </div>

                        {{-- Personal Email --}}
                        <div class="col-md-6">
                            <label for="personal_mailid" class="form-label ss-label">Personal Email</label>
                            <div class="input-group">
                                <span class="input-group-text ss-ig-text"><i class="bi bi-envelope"></i></span>
                                <input type="email"
                                       name="personal_mailid"
                                       id="personal_mailid"
                                       class="form-control ss-input @error('personal_mailid') is-invalid @enderror"
                                       placeholder="e.g. faculty.name@gmail.com"
                                       value="{{ old('personal_mailid') }}">
                            </div>
                            @error('personal_mailid')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Primary Phone --}}
                        <div class="col-md-6">
                            <label for="phone_primary" class="form-label ss-label required">Primary Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text ss-ig-text"><i class="bi bi-telephone-fill"></i></span>
                                <input type="text"
                                       name="phone_primary"
                                       id="phone_primary"
                                       class="form-control ss-input @error('phone_primary') is-invalid @enderror"
                                       placeholder="10-digit mobile number"
                                       value="{{ old('phone_primary') }}"
                                       required>
                            </div>
                            @error('phone_primary')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Secondary Phone --}}
                        <div class="col-md-6">
                            <label for="phone_secondary" class="form-label ss-label">Secondary / Alternate Phone</label>
                            <div class="input-group">
                                <span class="input-group-text ss-ig-text"><i class="bi bi-telephone"></i></span>
                                <input type="text"
                                       name="phone_secondary"
                                       id="phone_secondary"
                                       class="form-control ss-input @error('phone_secondary') is-invalid @enderror"
                                       placeholder="Alternate contact (optional)"
                                       value="{{ old('phone_secondary') }}">
                            </div>
                            @error('phone_secondary')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Organization & Role Mapping --}}
                <div class="ss-form-section mb-4">
                    <div class="ss-section-title">
                        <i class="bi bi-diagram-3 text-warning me-2"></i>
                        <span>3. Organization &amp; Role Mapping</span>
                    </div>

                    <div class="row g-3">
                        {{-- Designation --}}
                        <div class="col-md-4">
                            <label for="designation_id" class="form-label ss-label">Designation</label>
                            <select name="designation_id"
                                    id="designation_id"
                                    class="form-select ss-select @error('designation_id') is-invalid @enderror">
                                <option value="">-- Select Designation (Optional) --</option>
                                @foreach ($designations as $desig)
                                    <option value="{{ $desig->designation_id }}" {{ old('designation_id') == $desig->designation_id ? 'selected' : '' }}>
                                        {{ $desig->designation_name }} ({{ $desig->designation_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('designation_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campus --}}
                        <div class="col-md-4">
                            <label for="campus_id" class="form-label ss-label">Campus</label>
                            <select name="campus_id"
                                    id="campus_id"
                                    class="form-select ss-select @error('campus_id') is-invalid @enderror">
                                <option value="">-- Select Campus --</option>
                                @foreach ($campuses as $campus)
                                    <option value="{{ $campus->campus_id }}" {{ old('campus_id', '1') == $campus->campus_id ? 'selected' : '' }}>
                                        {{ $campus->campus_name }} ({{ $campus->campus_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('campus_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Primary Portal Role --}}
                        <div class="col-md-4">
                            <label for="role_id" class="form-label ss-label">Portal Role</label>
                            <select name="role_id"
                                    id="role_id"
                                    class="form-select ss-select @error('role_id') is-invalid @enderror">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->role_id }}" {{ old('role_id', '1') == $role->role_id ? 'selected' : '' }}>
                                        {{ $role->role_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SECTION 4: Login Account Provisioning --}}
                <div class="ss-form-section mb-2">
                    <div class="ss-section-title">
                        <i class="bi bi-key text-info me-2"></i>
                        <span>4. Portal Access &amp; Authentication</span>
                    </div>

                    <div class="p-3 rounded-3" style="background: var(--bg-elevated); border: 1px solid var(--border-subtle);">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   id="create_login"
                                   name="create_login"
                                   value="1"
                                   {{ old('create_login', '1') == '1' ? 'checked' : '' }}
                                   onchange="togglePasswordInput(this.checked)">
                            <label class="form-check-label fw-bold" for="create_login" style="color: var(--text-primary); cursor: pointer;">
                                Provision Portal Login Account for this Employee
                            </label>
                        </div>
                        <p class="small mb-3" style="color: var(--text-muted);">
                            Generates credentials in CDOE portal login system. Username will be set to the <strong>Employee Code</strong>.
                        </p>

                        <div id="passwordFields" style="{{ old('create_login', '1') == '1' ? 'display: block;' : 'display: none;' }}">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <label for="password" class="form-label ss-label">Initial Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text ss-ig-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="text"
                                               name="password"
                                               id="password"
                                               class="form-control ss-input font-monospace @error('password') is-invalid @enderror"
                                               placeholder="Default: Vignan@123"
                                               value="{{ old('password', 'Vignan@123') }}">
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text ss-help">Employee can change this on first login.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Buttons --}}
            <div class="ss-card-footer p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <a href="{{ route('hrms.empsearch') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                    Cancel
                </a>

                <div class="d-flex align-items-center gap-2">
                    <button type="reset" class="btn btn-outline-secondary px-3 py-2 rounded-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 ss-submit-btn" id="submitBtn">
                        <i class="bi bi-person-check-fill me-1"></i> Create Employee
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection

@push('styles')
<style>
    .ss-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: 999px;
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .ss-back-btn:hover {
        background: var(--bg-hover-row);
        color: var(--theme-primary);
        border-color: var(--border-hover);
        transform: translateX(-2px);
    }

    .ss-card {
        background: var(--bg-card);
        border: 1px solid var(--border-card);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: background 0.3s ease, border-color 0.3s ease;
    }

    .ss-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        background: var(--bg-elevated);
    }

    .ss-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--theme-gradient);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 14px var(--theme-glow);
    }

    .ss-form-section {
        padding-bottom: 20px;
        border-bottom: 1px dashed var(--border-subtle);
    }
    .ss-form-section:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .ss-section-title {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
    }

    .ss-label {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 5px;
    }
    .ss-label.required::after {
        content: " *";
        color: #ef4444;
        font-weight: 700;
    }

    .ss-input, .ss-select {
        height: 42px;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: 10px;
        color: var(--text-primary);
        font-size: 13px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        color-scheme: light;
    }
    .ss-select {
        padding: 8px 36px 8px 14px;
        cursor: pointer;
    }
    .ss-select option {
        background-color: var(--bg-card);
        color: var(--text-primary);
        padding: 10px 14px;
        font-size: 13.5px;
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
    .ss-input:hover, .ss-select:hover {
        border-color: var(--border-hover);
        background-color: var(--bg-hover-row);
    }
    .ss-input:focus, .ss-select:focus {
        background: var(--bg-card) !important;
        border-color: var(--theme-primary) !important;
        color: var(--text-primary) !important;
        box-shadow: 0 0 0 3px var(--theme-focus-ring) !important;
        outline: none !important;
    }
    .ss-input::placeholder {
        color: var(--text-muted);
        font-size: 12.5px;
    }

    .ss-ig-text {
        background: var(--bg-elevated);
        border: 1px solid var(--border-subtle);
        border-right: none;
        color: var(--text-muted);
        font-size: 14px;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }
    .ss-input.form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .ss-help {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .ss-card-footer {
        background: var(--bg-elevated);
        border-top: 1px solid var(--border-subtle);
    }

    .ss-submit-btn {
        background: var(--theme-gradient) !important;
        border-color: var(--theme-primary) !important;
        box-shadow: 0 4px 14px var(--theme-glow);
        font-weight: 600;
        font-size: 13.5px;
        transition: all 0.2s ease;
    }
    .ss-submit-btn:hover {
        filter: brightness(1.1);
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
    function togglePasswordInput(show) {
        const el = document.getElementById('passwordFields');
        if (el) {
            el.style.display = show ? 'block' : 'none';
        }
    }

    // Auto uppercase employee code
    document.getElementById('emp_code')?.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Form submit loading indicator
    document.getElementById('empCreationForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving Employee...';
        }
    });
</script>
@endpush
