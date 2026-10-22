@forelse ($students as $s)
    <tr>
        <td>
            <span class="ss-badge-reg">{{ $s->register_no }}</span>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="ss-avatar-mini">
                    {{ strtoupper(substr($s->student_name, 0, 1)) }}
                </div>
                <div>
                    <span class="ss-name">{{ $s->student_name }}</span>
                    @if(!empty($s->program_code))
                        <div class="ss-sub-info text-muted">{{ $s->program_code }} {{ !empty($s->gender) ? '• ' . $s->gender : '' }}</div>
                    @endif
                </div>
            </div>
        </td>
        <td>
            <span class="ss-badge-batch">{{ $s->batch_code ?? '-' }}</span>
        </td>
        <td>
            <span class="ss-badge-regu">{{ $s->regulation ?? '-' }}</span>
        </td>
        <td class="text-center">
            <button type="button"
                    class="ss-view-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#studentModal"
                    data-regno="{{ $s->register_no }}"
                    data-name="{{ $s->student_name }}"
                    data-batch="{{ $s->batch_code ?? '-' }}"
                    data-regulation="{{ $s->regulation ?? '-' }}"
                    data-status="{{ $s->status ?? '-' }}"
                    data-gender="{{ $s->gender ?? '-' }}"
                    data-dob="{{ $s->date_of_birth ?? '-' }}"
                    data-email="{{ $s->email ?? '-' }}"
                    data-mobile="{{ $s->mobile_no ?? '-' }}"
                    data-program="{{ $s->program_name ?? '-' }}"
                    data-program-code="{{ $s->program_code ?? '-' }}"
                    data-admission="{{ $s->admission_date ?? '-' }}"
                    data-appno="{{ $s->application_no ?? '-' }}"
                    data-ofc-mail="{{ $s->ofc_mail ?? '-' }}"
                    data-std-type="{{ $s->std_type ?? '-' }}"
                    data-enrollment="{{ $s->enrollment_no ?? '-' }}"
                    data-aadhar="{{ $s->aadhar_no ?? '-' }}"
                    data-abc="{{ $s->abc_id ?? '-' }}"
                    data-deb="{{ $s->deb_id ?? '-' }}">
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
        <td colspan="5" class="ss-empty">
            <div class="ss-empty-icon">🔍</div>
            <strong>No students found</strong>
            <p>Try searching with a different keyword, register number, or clearing active filters.</p>
        </td>
    </tr>
@endforelse
