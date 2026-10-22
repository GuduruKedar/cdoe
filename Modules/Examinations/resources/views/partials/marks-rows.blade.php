@forelse ($marksMemoList as $memo)
    <tr>
        <td>
            <span class="ss-badge-reg font-monospace">{{ $memo->regno }}</span>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="ss-avatar-mini">
                    {{ strtoupper(substr($memo->student_name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <span class="ss-name">{{ $memo->student_name ?? 'Student' }}</span>
                    @if(!empty($memo->mmno))
                        <div class="ss-sub-info font-monospace text-muted">{{ $memo->mmno }}</div>
                    @endif
                </div>
            </div>
        </td>
        <td>
            <span class="ss-badge-regu">
                Y{{ $memo->s_year ?? 1 }} • S{{ $memo->s_sem ?? 1 }}
            </span>
        </td>
        <td>
            <span class="ss-badge-batch">
                {{ $memo->mm_type }}
            </span>
        </td>
        <td>
            @php
                $div = strtoupper($memo->division_name ?? '');
                $badgeClass = 'div-other';
                if (str_contains($div, 'DISTINCTION') || str_contains($div, 'FIRST')) {
                    $badgeClass = 'div-first';
                } elseif (str_contains($div, 'SECOND')) {
                    $badgeClass = 'div-second';
                } elseif (str_contains($div, 'FAIL')) {
                    $badgeClass = 'div-fail';
                }
            @endphp
            <span class="ss-div-pill {{ $badgeClass }}">
                {{ $memo->division_name }}
            </span>
        </td>
        <td>
            <span class="text-muted small">
                {{ str_replace('_', ' ', $memo->exam_mon_yyyy) }}
            </span>
        </td>
        <td>
            <div class="d-flex align-items-center gap-1 font-monospace">
                <span class="fw-bold text-primary">{{ $memo->sgpa_perc ?? '-' }}</span>
                <span class="text-muted">/</span>
                <span class="text-secondary">{{ $memo->cgpa_perc ?? '-' }}</span>
            </div>
        </td>
        <td class="text-center">
            @php
                $memoParam = $memo->mmno . '_' . $memo->regno . (!empty($memo->appno) ? '_' . $memo->appno : '');
            @endphp
            <a href="{{ route('examinations.results.memo', ['memo' => $memoParam]) }}"
               target="_blank"
               class="ss-view-btn"
               title="View Marks Memo">
                <span>View</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                </svg>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="ss-empty">
            <div class="ss-empty-icon">📄</div>
            <strong>No student marks records found</strong>
            <p>Try searching with a different register number, student name, memo number, or clearing active filters.</p>
        </td>
    </tr>
@endforelse
