@forelse ($logs as $log)
    @php
        $moduleColor = match(strtoupper($log->module ?? 'SYSTEM')) {
            'AUTH' => 'purple',
            'HRMS' => 'green',
            'ADMISSIONS' => 'blue',
            'EXAMINATIONS' => 'amber',
            default => 'crimson'
        };

        $actionIcon = match(strtoupper($log->action ?? '')) {
            'LOGIN' => 'bi-box-arrow-in-right',
            'LOGOUT' => 'bi-box-arrow-right',
            'SWITCH_ROLE' => 'bi-arrow-left-right',
            'CREATE_EMPLOYEE', 'CREATE' => 'bi-person-plus-fill',
            'SEARCH' => 'bi-search',
            'VIEW' => 'bi-eye-fill',
            'UPDATE' => 'bi-pencil-square',
            'DELETE' => 'bi-trash-fill',
            default => 'bi-activity'
        };

        $statusColor = match(strtolower($log->status ?? 'success')) {
            'success' => 'success',
            'warning' => 'warning',
            'failed', 'error' => 'danger',
            default => 'info'
        };
    @endphp
    <tr>
        {{-- Timestamp --}}
        <td>
            <div class="d-flex flex-column">
                <span class="ss-name" style="font-size: 13px; font-weight: 600;">
                    {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                </span>
                <span class="text-muted font-monospace" style="font-size: 11px;">
                    {{ $log->created_at ? $log->created_at->format('M d, Y • H:i:s') : '-' }}
                </span>
            </div>
        </td>

        {{-- User Details --}}
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="ss-avatar-mini" style="background: var(--theme-gradient); color: #fff;">
                    {{ strtoupper(substr($log->user_code ?? ($log->user_name ?? 'U'), 0, 1)) }}
                </div>
                <div>
                    <div class="ss-name font-monospace" style="font-weight: 700;">
                        {{ $log->user_code ?? 'System / Guest' }}
                    </div>
                    <div class="ss-sub-info text-muted">
                        {{ $log->role_name ?? ($log->user_name ?? 'CDOE Member') }}
                    </div>
                </div>
            </div>
        </td>

        {{-- Module & Action --}}
        <td>
            <div class="d-flex flex-column gap-1">
                <span class="dash-stat-tag {{ $moduleColor }}" style="align-self: flex-start; padding: 2px 8px; font-size: 10px; border-radius: 6px;">
                    {{ $log->module ?? 'SYSTEM' }}
                </span>
                <div class="d-flex align-items-center gap-1 font-monospace" style="font-size: 12px; font-weight: 600; color: var(--text-primary);">
                    <i class="bi {{ $actionIcon }} text-primary"></i>
                    <span>{{ $log->action ?? 'ACTION' }}</span>
                </div>
            </div>
        </td>

        {{-- Description --}}
        <td>
            <div class="text-wrap" style="max-width: 380px; font-size: 13px; color: var(--text-secondary); line-height: 1.45;">
                {{ $log->description ?? '-' }}
            </div>
        </td>

        {{-- Client IP & Device --}}
        <td>
            <div class="d-flex flex-column">
                <span class="font-monospace text-muted" style="font-size: 12px;">
                    <i class="bi bi-geo-alt me-1 text-primary"></i>{{ $log->ip_address ?? '127.0.0.1' }}
                </span>
                <span class="text-muted text-truncate" style="font-size: 11px; max-width: 180px;" title="{{ $log->device_type ?? $log->user_agent }}">
                    {{ $log->device_type ?? 'Desktop / Web' }}
                </span>
            </div>
        </td>

        {{-- Status --}}
        <td>
            @if(strtolower($log->status ?? 'success') === 'success')
                <span class="ss-status-badge ss-status-active">
                    <span class="ss-dot-pulse"></span> Success
                </span>
            @elseif(strtolower($log->status ?? '') === 'warning')
                <span class="ss-status-badge" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.25);">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Warning
                </span>
            @else
                <span class="ss-status-badge ss-status-inactive">
                    <i class="bi bi-x-circle-fill me-1"></i> {{ ucfirst($log->status ?? 'Failed') }}
                </span>
            @endif
        </td>

        {{-- Action: Inspect Modal --}}
        <td class="text-end">
            <button type="button"
                    class="ss-view-btn inspect-log-btn"
                    data-log-id="{{ $log->id }}"
                    style="padding: 4px 10px; font-size: 11px;">
                <i class="bi bi-info-circle me-1"></i>
                <span>Inspect</span>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                <div class="ss-avatar-mini" style="width: 48px; height: 48px; font-size: 20px; background: var(--bg-hover-row); color: var(--text-muted);">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="fw-semibold text-muted">No activity log entries found</div>
                <small class="text-muted">Try adjusting your search query, module filter, or date range.</small>
            </div>
        </td>
    </tr>
@endforelse
