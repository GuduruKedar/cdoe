@forelse ($employees as $emp)
    <tr>
        <td>
            <span class="ss-badge-reg font-monospace">{{ $emp->emp_code }}</span>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="ss-avatar-mini">
                    {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                </div>
                <div>
                    <span class="ss-name">{{ $emp->full_name }}</span>
                    @if(!empty($emp->emp_type))
                        <div class="ss-sub-info text-muted">{{ $emp->emp_type }}</div>
                    @endif
                </div>
            </div>
        </td>
        <td>
            @if(!empty($emp->email))
                <a href="mailto:{{ $emp->email }}" class="ss-email-link">
                    <i class="bi bi-envelope me-1"></i>{{ $emp->email }}
                </a>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if(!empty($emp->phone))
                <a href="tel:{{ $emp->phone }}" class="ss-phone-link">
                    <i class="bi bi-telephone me-1"></i>{{ $emp->phone }}
                </a>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($emp->is_active)
                <span class="ss-status-badge ss-status-active">
                    <span class="ss-dot-pulse"></span> Active
                </span>
            @else
                <span class="ss-status-badge ss-status-inactive">
                    Inactive
                </span>
            @endif
        </td>
        <td class="text-center">
            <button type="button"
                    class="ss-view-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#employeeModal"
                    data-code="{{ $emp->emp_code }}"
                    data-name="{{ $emp->full_name }}"
                    data-email="{{ $emp->email ?? '-' }}"
                    data-personal-email="{{ $emp->personal_mailid ?? '-' }}"
                    data-phone="{{ $emp->phone ?? '-' }}"
                    data-phone-secondary="{{ $emp->phone_secondary ?? '-' }}"
                    data-type="{{ $emp->emp_type ?? 'PERMANENT' }}"
                    data-status="{{ $emp->is_active ? 'Active' : 'Inactive' }}"
                    data-doj="{{ $emp->date_of_join ?? '-' }}">
                <span>View</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="ss-empty">
            <div class="ss-empty-icon">🔍</div>
            <strong>No employees found</strong>
            <p>Try searching with a different employee code, name, email, or clearing active filters.</p>
        </td>
    </tr>
@endforelse
