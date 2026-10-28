@extends('layouts.app')

@section('hideTitle', true)

@section('hidePageSub', true)

@section('content')

    {{-- Alert if previewing another student's marks --}}
    @if (!empty($reg_no) && !empty($sessionCode) && $reg_no !== $sessionCode)
        <div class="preview-student-alert mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <i class="bi bi-eye-fill me-2 text-primary"></i>
                    <span>Previewing examination marks for Student: <strong>{{ $reg_no }}</strong></span>
                </div>
                <a href="{{ route('examinations.mmview') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Return to My Account
                </a>
            </div>
        </div>
    @endif

    {{-- Friendly helper banner if current student has no uploaded marks yet --}}
    @if ($marksMemoList->isEmpty() && empty(request('regno')))
        <div class="preview-student-alert info mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <i class="bi bi-info-circle-fill me-2 text-info"></i>
                    <span>No examination memos uploaded yet for Student ID <strong>{{ $reg_no }}</strong>.</span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small">Preview sample records:</span>
                    <a href="{{ route('examinations.mmview', ['regno' => '261CK01066']) }}" class="badge-demo-student">
                        <i class="bi bi-person-badge me-1"></i> 261CK01066 (Regular + Reappear)
                    </a>
                    <a href="{{ route('examinations.mmview', ['regno' => '261CK01039']) }}" class="badge-demo-student">
                        <i class="bi bi-person-badge me-1"></i> 261CK01039 (Active Backlog)
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= 1. GRADE STATISTICS & HOVER BREAKDOWN ================= --}}
    <div class="grade-stats-wrapper mb-4">
        <div class="grade-stats-header">
            <div>
                <h5 class="grade-stats-title">
                    <i class="bi bi-award-fill me-2 text-primary"></i>
                    Subject Grade Distribution
                </h5>
                <p class="grade-stats-subtitle">
                    Overall performance across all semesters • Hover over any grade card for semester-wise breakdown
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="grade-summary-chips">
                    <div class="summary-chip total" title="Total unique curriculum subjects attempted">
                        <span class="chip-label">Total</span>
                        <span class="chip-value">{{ $gradeStats['summary']['total_subjects'] ?? 0 }}</span>
                    </div>
                    <div class="summary-chip passed" title="Total unique subjects passed">
                        <span class="chip-label">Passed</span>
                        <span class="chip-value text-success">{{ $gradeStats['summary']['total_passed'] ?? 0 }}</span>
                    </div>
                    <div class="summary-chip backlogs {{ ($gradeStats['summary']['total_backlogs'] ?? 0) > 0 ? 'has-backlogs' : 'clear' }}"
                         title="Current pending backlogs (subjects not yet passed)">
                        <span class="chip-label">Backlogs</span>
                        <span class="chip-value">{{ $gradeStats['summary']['total_backlogs'] ?? 0 }}</span>
                    </div>
                    @if (($gradeStats['summary']['total_cleared'] ?? 0) > 0)
                        <div class="summary-chip cleared" title="Subjects failed initially in regular, then passed in reappear">
                            <span class="chip-label">Cleared</span>
                            <span class="chip-value text-emerald">{{ $gradeStats['summary']['total_cleared'] }}</span>
                        </div>
                    @endif
                </div>

                {{-- Quick Minimize / Expand Toggle Button --}}
                <button type="button" class="btn-toggle-stats" id="toggleGradeStatsBtn" title="Minimize / Expand Grade Boxes">
                    <i class="bi bi-dash-lg" id="toggleIcon"></i>
                    <span id="toggleText">Minimize</span>
                </button>
            </div>
        </div>

        {{-- Grade Cards Grid: Single Row (7-Column) Layout --}}
        <div class="grade-cards-grid" id="gradeCardsGrid">
            @foreach ($gradeStats['grades'] as $g => $data)
                <div class="grade-card grade-card-{{ strtolower($g) }}" tabindex="0" data-grade="{{ $g }}">
                    {{-- 2 Columns Inside Each Grade Card --}}
                    <div class="grade-card-2col">
                        {{-- Column 1 (Left): Grade Identification & Points --}}
                        <div class="grade-col-left">
                            <span class="grade-letter-pill grade-pill-{{ strtolower($g) }}">{{ $g }}</span>
                            <div class="grade-name-text text-truncate" title="{{ $data['name'] }}">
                                {{ $data['name'] }}
                            </div>
                            @if ($g === 'F' && $data['cleared_in_reappear'] > 0)
                                <span class="badge-cleared-chip" title="{{ $data['cleared_in_reappear'] }} failed in regular, passed in reappear">
                                    <i class="bi bi-check"></i>{{ $data['cleared_in_reappear'] }} Clr
                                </span>
                            @else
                                <span class="grade-gp-badge">{{ $data['points'] }} GP</span>
                            @endif
                        </div>

                        {{-- Column 2 (Right): Subject Count & Semesters Trigger --}}
                        <div class="grade-col-right">
                            <div class="grade-number {{ $g === 'F' ? ($data['active_backlogs'] > 0 ? 'text-danger' : 'text-success') : '' }}">
                                {{ $g === 'F' ? $data['active_backlogs'] : $data['effective_count'] }}
                            </div>
                            <div class="grade-metric-label">
                                {{ $g === 'F' ? ($data['active_backlogs'] === 1 ? 'Backlog' : 'Backlogs') : ($data['effective_count'] === 1 ? 'Subject' : 'Subjects') }}
                            </div>
                            <div class="grade-sem-link" title="Hover for semester breakdown">
                                <span>Sem</span>
                                <i class="bi bi-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    {{-- ================= HOVER POPOVER CARD ================= --}}
                    <div class="grade-hover-popover">
                        <div class="popover-arrow"></div>
                        <div class="popover-head">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="grade-letter-mini grade-pill-{{ strtolower($g) }}">{{ $g }}</span>
                                    <div>
                                        <div class="popover-title-text">Grade {{ $g }} Breakdown</div>
                                        <div class="popover-sub-text">{{ $data['name'] }}</div>
                                    </div>
                                </div>
                                <span class="popover-badge-count">
                                    @if ($g === 'F')
                                        {{ $data['active_backlogs'] > 0 ? ($data['active_backlogs'] . ' Pending') : '0 Pending' }}
                                    @else
                                        {{ $data['effective_count'] }} {{ Str::plural('Subject', $data['effective_count']) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="popover-body">
                            @if (empty($data['semesters']))
                                <div class="popover-empty">
                                    <i class="bi bi-inbox text-muted"></i>
                                    <div>No {{ $g }} grades recorded across any semester</div>
                                </div>
                            @else
                                <div class="popover-sem-list">
                                    @foreach ($data['semesters'] as $sem => $semData)
                                        <div class="popover-sem-item">
                                            <div class="popover-sem-header">
                                                <span class="sem-name-pill">
                                                    <i class="bi bi-mortarboard-fill me-1"></i> Semester {{ $sem }}
                                                </span>
                                                <span class="sem-total-count">
                                                    {{ $semData['total'] }} {{ Str::plural('attempt', $semData['total']) }}
                                                </span>
                                            </div>

                                            <div class="popover-attempts-bar">
                                                <div class="attempt-badge regular" title="Regular exam attempts">
                                                    <span class="attempt-circle reg"></span>
                                                    <span>Regular: <strong>{{ $semData['regular'] }}</strong></span>
                                                </div>
                                                <div class="attempt-badge reappear" title="Reappear exam attempts">
                                                    <span class="attempt-circle reap"></span>
                                                    <span>Reappear: <strong>{{ $semData['reappear'] }}</strong></span>
                                                </div>
                                            </div>

                                            @if ($g === 'F')
                                                <div class="popover-f-status">
                                                    @if ($semData['cleared'] > 0)
                                                        <span class="f-pill cleared" title="Failed initially in regular, then passed in reappear">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                            {{ $semData['cleared'] }} Passed in Reappear
                                                        </span>
                                                    @endif
                                                    @if ($semData['active'] > 0)
                                                        <span class="f-pill active" title="Still not cleared">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                            {{ $semData['active'] }} Active Backlog
                                                        </span>
                                                    @elseif ($semData['cleared'] > 0 && $semData['active'] === 0)
                                                        <span class="f-pill all-passed" title="All regular failures in this semester cleared!">
                                                            <i class="bi bi-shield-check"></i>
                                                            All Cleared
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="popover-foot">
                            @if ($g === 'F')
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <span class="popover-foot-label">Current Status:</span>
                                    <span class="popover-foot-value {{ $data['active_backlogs'] > 0 ? 'text-danger' : 'text-success' }}">
                                        @if ($data['active_backlogs'] > 0)
                                            <i class="bi bi-exclamation-circle-fill me-1"></i>
                                            {{ $data['active_backlogs'] }} Pending Backlog{{ $data['active_backlogs'] > 1 ? 's' : '' }}
                                        @else
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            No Active Backlogs
                                        @endif
                                    </span>
                                </div>
                            @else
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <span class="popover-foot-label">Total Graded:</span>
                                    <span class="popover-foot-value text-primary">
                                        {{ $data['effective_count'] }} {{ Str::plural('Subject', $data['effective_count']) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ================= 2. MARKS MEMO TABLE ================= --}}
    <div class="memo-card">
        <div class="memo-card-header">
            <div>
                <h5>Marks Memo History</h5>
                <p>Academic performance and examination records</p>
            </div>
            <span class="memo-count">
                {{ count($marksMemoList) }} Records
            </span>
        </div>

        <div class="table-responsive">
            <table class="table memo-table">
                <thead>
                <tr>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Exam Type</th>
                    <th>Division</th>
                    <th>Exam Month &amp; Year</th>
                    <th>SGPA</th>
                    <th>CGPA</th>
                    <th class="text-center">Action</th>
                </tr>
                </thead>

                <tbody>
                @forelse ($marksMemoList as $memo)
                    <tr>
                        <td>
                            <span class="year-badge">
                                Year {{ $memo->s_year }}
                            </span>
                        </td>

                        <td>Semester {{ $memo->s_sem }}</td>

                        <td>
                            <span class="exam-type">
                                {{ $memo->mm_type }}
                            </span>
                        </td>

                        <td>
                            <span class="division-text">
                                {{ $memo->division_name }}
                            </span>
                        </td>

                        <td>
                            {{ str_replace('_', ' ', $memo->exam_mon_yyyy) }}
                        </td>

                        <td>
                            <span class="score">
                                {{ $memo->sgpa_perc }}
                            </span>
                        </td>

                        <td>
                            <span class="score score-primary">
                                {{ $memo->cgpa_perc }}
                            </span>
                        </td>

                        <td class="text-center">
                            <a href="{{ route('examinations.results.memo', ['memo' => $memo->mmno."_".$memo->regno."_".$memo->appno]) }}"
                               target="_blank"
                               class="view-btn">
                                <span>View</span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     width="15" height="15"
                                     viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="2"
                                     stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <div class="empty-icon">📄</div>
                            <strong>No marks memos found</strong>
                            <p>Examination records will appear here when available.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .memo-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            color: var(--text-secondary);
            transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .memo-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .memo-card-header h5 {
            margin: 0 0 5px;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .memo-card-header p {
            margin: 0;
            font-size: 13px;
            color: var(--text-muted);
        }

        .memo-count {
            white-space: nowrap;
            background: var(--theme-primary-light);
            color: var(--theme-primary);
            border: 1px solid var(--theme-focus-ring);
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .memo-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            background: transparent;
        }

        .memo-table thead th {
            background: var(--bg-elevated);
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .45px;
            white-space: nowrap;
            padding: 15px 18px;
            border: none;
            border-bottom: 1px solid var(--border-subtle);
        }

        .memo-table tbody td {
            padding: 16px 18px;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid var(--border-subtle);
            color: var(--text-secondary);
            white-space: nowrap;
            background: transparent;
        }

        .memo-table tbody tr {
            transition: background-color .2s ease;
        }

        .memo-table tbody tr:hover {
            background: var(--bg-hover-row);
        }

        .memo-table tbody tr:last-child td {
            border-bottom: none;
        }

        .year-badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 6px;
            background: var(--bg-elevated);
            color: var(--text-secondary);
            border: 1px solid var(--border-subtle);
            font-weight: 600;
            font-size: 12px;
        }

        .exam-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.25);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        html.dark .exam-type {
            background: rgba(245, 158, 11, 0.18);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.35);
        }

        .division-text {
            color: var(--text-primary);
            font-weight: 600;
        }

        .score {
            font-weight: 700;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
        }

        .score-primary {
            color: var(--theme-primary);
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 14px;
            border-radius: 8px;
            background: var(--theme-gradient);
            box-shadow: 0 4px 12px var(--theme-glow);
            color: #fff !important;
            text-decoration: none !important;
            font-size: 12px;
            font-weight: 600;
            transition: all .2s ease;
        }

        .view-btn:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px var(--theme-glow);
        }

        .empty-state {
            text-align: center;
            padding: 42px 16px !important;
            white-space: normal !important;
        }

        .empty-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .empty-state strong {
            display: block;
            color: var(--text-primary);
            font-size: 15px;
        }

        .empty-state p {
            margin: 6px 0 0;
            color: var(--text-muted);
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .memo-card-header {
                padding: 17px;
            }

            .memo-table thead th,
            .memo-table tbody td {
                padding: 12px;
            }
        }

        /* ================= GRADE STATISTICS & HOVER POPOVERS ================= */
        .preview-student-alert {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-left: 4px solid var(--theme-primary);
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 13px;
            color: var(--text-secondary);
            box-shadow: var(--card-shadow);
        }
        .preview-student-alert.info {
            border-left-color: #0ea5e9;
            background: rgba(14, 165, 233, 0.05);
        }
        .badge-demo-student {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            background: var(--bg-elevated);
            color: var(--text-primary);
            border: 1px solid var(--border-subtle);
            transition: all 0.2s ease;
        }
        .badge-demo-student:hover {
            background: var(--theme-primary-light);
            color: var(--theme-primary);
            border-color: var(--theme-primary);
            transform: translateY(-1px);
        }

        .grade-stats-wrapper {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: var(--card-shadow);
            transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .grade-stats-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .grade-stats-title {
            margin: 0 0 2px;
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
        }

        .grade-stats-subtitle {
            margin: 0;
            font-size: 11.5px;
            color: var(--text-muted);
        }

        .grade-summary-chips {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            font-size: 11.5px;
        }

        .summary-chip .chip-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .summary-chip .chip-value {
            font-weight: 700;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
        }

        .summary-chip.backlogs.has-backlogs {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
        }
        .summary-chip.backlogs.has-backlogs .chip-value {
            color: #ef4444;
        }
        .summary-chip.backlogs.clear .chip-value {
            color: #10b981;
        }

        .text-emerald { color: #10b981 !important; }

        .btn-toggle-stats {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-subtle);
            background: var(--bg-elevated);
            color: var(--text-secondary);
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-toggle-stats:hover {
            background: var(--theme-primary-light);
            color: var(--theme-primary);
            border-color: var(--theme-primary);
        }

        /* ================= SINGLE-ROW (7-COLUMN) GRID ================= */
        .grade-cards-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(115px, 1fr));
            gap: 8px;
            transition: all 0.25s ease;
        }

        .grade-cards-grid.is-minimized {
            display: none !important;
        }

        .grade-card {
            position: relative;
            background: var(--bg-elevated);
            border: 1.5px solid var(--border-subtle);
            border-top: 3.5px solid transparent;
            border-radius: 10px;
            padding: 9px 10px;
            cursor: pointer;
            outline: none;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .grade-card:hover,
        .grade-card:focus-visible,
        .grade-card.popover-open {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            border-color: var(--border-card);
        }

        /* Distinct Top Accent Bars per Grade */
        .grade-card-o { border-top-color: #8b5cf6; }
        .grade-card-s { border-top-color: #3b82f6; }
        .grade-card-a { border-top-color: #0ea5e9; }
        .grade-card-b { border-top-color: #10b981; }
        .grade-card-c { border-top-color: #f59e0b; }
        .grade-card-d { border-top-color: #f97316; }
        .grade-card-f { border-top-color: #ef4444; }

        /* ================= 2-COLUMN LAYOUT INSIDE EACH GRADE CARD ================= */
        .grade-card-2col {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 8px;
            height: 100%;
        }

        .grade-col-left {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            min-width: 0;
            flex: 1;
            gap: 4px;
        }

        .grade-col-right {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            flex-shrink: 0;
            text-align: right;
            gap: 4px;
        }

        .grade-letter-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 7px;
            font-weight: 800;
            font-size: 13.5px;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
        }

        .grade-pill-o { background: linear-gradient(135deg, #a855f7, #7c3aed); }
        .grade-pill-s { background: linear-gradient(135deg, #60a5fa, #2563eb); }
        .grade-pill-a { background: linear-gradient(135deg, #38bdf8, #0284c7); }
        .grade-pill-b { background: linear-gradient(135deg, #34d399, #059669); }
        .grade-pill-c { background: linear-gradient(135deg, #fbbf24, #d97706); }
        .grade-pill-d { background: linear-gradient(135deg, #fb923c, #ea580c); }
        .grade-pill-f { background: linear-gradient(135deg, #f87171, #dc2626); }

        .grade-name-text {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--text-primary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
            line-height: 1.2;
        }

        .grade-gp-badge {
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            background: rgba(148, 163, 184, 0.14);
            padding: 1.5px 6px;
            border-radius: 4px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .grade-number {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .grade-metric-label {
            font-size: 10.5px;
            font-weight: 600;
            color: var(--text-muted);
            white-space: nowrap;
            line-height: 1.2;
        }

        .badge-cleared-chip {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            font-size: 9.5px;
            font-weight: 700;
            color: #059669;
            background: rgba(16, 185, 129, 0.12);
            padding: 1px 5px;
            border-radius: 4px;
            white-space: nowrap;
        }
        html.dark .badge-cleared-chip {
            color: #34d399;
            background: rgba(52, 211, 153, 0.14);
        }

        .grade-sem-link {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            font-size: 10.5px;
            font-weight: 600;
            color: var(--theme-primary);
            white-space: nowrap;
            transition: color 0.15s ease;
            line-height: 1.2;
        }
        .grade-card:hover .grade-sem-link,
        .grade-card.popover-open .grade-sem-link {
            color: var(--theme-primary-hover);
            font-weight: 700;
        }

        /* ================= HOVER POPOVER CARD ================= */
        .grade-hover-popover {
            position: absolute;
            top: calc(100% + 8px);
            min-width: 310px;
            max-width: 360px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.2), 0 4px 12px rgba(15, 23, 42, 0.08);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            z-index: 1050;
            transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.2s ease;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        /* Popover positioning across the 7 columns in single row */
        .grade-card:nth-child(1) .grade-hover-popover,
        .grade-card:nth-child(2) .grade-hover-popover,
        .grade-card:nth-child(3) .grade-hover-popover {
            left: 0;
            right: auto;
            transform: translateY(6px);
        }
        .grade-card:nth-child(1) .popover-arrow,
        .grade-card:nth-child(2) .popover-arrow,
        .grade-card:nth-child(3) .popover-arrow {
            left: 20px;
            right: auto;
        }

        .grade-card:nth-child(4) .grade-hover-popover {
            left: 50%;
            right: auto;
            transform: translateX(-50%) translateY(6px);
        }
        .grade-card:nth-child(4) .popover-arrow {
            left: 50%;
            margin-left: -5px;
            right: auto;
        }

        .grade-card:nth-child(5) .grade-hover-popover,
        .grade-card:nth-child(6) .grade-hover-popover,
        .grade-card:nth-child(7) .grade-hover-popover {
            right: 0;
            left: auto;
            transform: translateY(6px);
        }
        .grade-card:nth-child(5) .popover-arrow,
        .grade-card:nth-child(6) .popover-arrow,
        .grade-card:nth-child(7) .popover-arrow {
            right: 20px;
            left: auto;
        }

        .grade-card:hover .grade-hover-popover,
        .grade-card:focus-within .grade-hover-popover,
        .grade-card.popover-open .grade-hover-popover {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }

        .grade-card:nth-child(4):hover .grade-hover-popover,
        .grade-card:nth-child(4):focus-within .grade-hover-popover,
        .grade-card:nth-child(4).popover-open .grade-hover-popover {
            transform: translateX(-50%) translateY(0);
        }

        /* Hover gap bridge so moving mouse to popover does not lose hover */
        .grade-hover-popover::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            right: 0;
            height: 10px;
            background: transparent;
        }

        /* Popover arrow */
        .popover-arrow {
            position: absolute;
            top: -5px;
            right: 48px;
            left: auto;
            transform: rotate(45deg);
            width: 10px;
            height: 10px;
            background: var(--bg-card);
            border-top: 1px solid var(--border-card);
            border-left: 1px solid var(--border-card);
            z-index: 2;
        }

        /* Activation state on hover / focus / tap */
        .grade-card:hover .grade-hover-popover,
        .grade-card:focus-within .grade-hover-popover,
        .grade-card.popover-open .grade-hover-popover {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }

        .popover-head {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .grade-letter-mini {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            color: #fff;
        }

        .popover-title-text {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .popover-sub-text {
            font-size: 11px;
            color: var(--text-muted);
        }

        .popover-badge-count {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 9px;
            border-radius: 20px;
            background: var(--bg-elevated);
            color: var(--text-primary);
            border: 1px solid var(--border-subtle);
        }

        .popover-body {
            padding: 12px 14px;
            max-height: 260px;
            overflow-y: auto;
        }

        .popover-empty {
            text-align: center;
            padding: 18px 8px;
            color: var(--text-muted);
            font-size: 12px;
        }
        .popover-empty i {
            font-size: 24px;
            margin-bottom: 6px;
        }

        .popover-sem-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .popover-sem-item {
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: 9px;
            padding: 10px 12px;
            transition: background 0.15s ease;
        }
        .popover-sem-item:hover {
            background: var(--bg-hover-row);
        }

        .popover-sem-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .sem-name-pill {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .sem-total-count {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .popover-attempts-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .attempt-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            color: var(--text-secondary);
        }

        .attempt-circle {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }
        .attempt-circle.reg { background: #3b82f6; }
        .attempt-circle.reap { background: #f59e0b; }

        .popover-f-status {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed var(--border-subtle);
        }

        .f-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 5px;
        }
        .f-pill.cleared {
            color: #059669;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .f-pill.active {
            color: #dc2626;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .f-pill.all-passed {
            color: #0284c7;
            background: rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.25);
        }

        .popover-foot {
            padding: 10px 14px;
            background: var(--bg-elevated);
            border-top: 1px solid var(--border-subtle);
            border-radius: 0 0 14px 14px;
            font-size: 11.5px;
        }
        .popover-foot-label {
            color: var(--text-muted);
        }
        .popover-foot-value {
            font-weight: 700;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .grade-cards-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 768px) {
            .grade-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .grade-stats-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .grade-summary-chips {
                width: 100%;
            }
            .grade-card:first-child .grade-hover-popover,
            .grade-card:nth-child(2) .grade-hover-popover,
            .grade-card:nth-last-child(-n+2) .grade-hover-popover,
            .grade-hover-popover {
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%) translateY(8px) !important;
            }
            .grade-card:hover .grade-hover-popover,
            .grade-card.popover-open .grade-hover-popover {
                transform: translateX(-50%) translateY(0) !important;
            }
            .popover-arrow {
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%) rotate(45deg) !important;
            }
        }

        @media (max-width: 480px) {
            .grade-cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- Interactive Click/Tap support for touch devices, keyboard navigation & Minimize toggle --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Minimize / Expand Toggle
            const toggleBtn = document.getElementById('toggleGradeStatsBtn');
            const cardsGrid = document.getElementById('gradeCardsGrid');
            const toggleIcon = document.getElementById('toggleIcon');
            const toggleText = document.getElementById('toggleText');

            if (toggleBtn && cardsGrid) {
                // Restore saved preference
                const isSavedMin = localStorage.getItem('cdoe_grade_boxes_minimized') === 'true';
                if (isSavedMin) {
                    cardsGrid.classList.add('is-minimized');
                    toggleIcon.className = 'bi bi-plus-lg';
                    toggleText.textContent = 'Expand';
                }

                toggleBtn.addEventListener('click', function () {
                    const isMin = cardsGrid.classList.toggle('is-minimized');
                    if (isMin) {
                        toggleIcon.className = 'bi bi-plus-lg';
                        toggleText.textContent = 'Expand';
                        localStorage.setItem('cdoe_grade_boxes_minimized', 'true');
                    } else {
                        toggleIcon.className = 'bi bi-dash-lg';
                        toggleText.textContent = 'Minimize';
                        localStorage.setItem('cdoe_grade_boxes_minimized', 'false');
                    }
                });
            }

            const gradeCards = document.querySelectorAll('.grade-card');

            gradeCards.forEach(card => {
                // Click or Tap toggle
                card.addEventListener('click', function (e) {
                    if (e.target.closest('.grade-hover-popover')) {
                        return; // Allow interacting with popover content
                    }
                    const isOpen = card.classList.contains('popover-open');
                    gradeCards.forEach(c => c.classList.remove('popover-open'));
                    if (!isOpen) {
                        card.classList.add('popover-open');
                    }
                });

                // Enter or Space key navigation
                card.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const isOpen = card.classList.contains('popover-open');
                        gradeCards.forEach(c => c.classList.remove('popover-open'));
                        if (!isOpen) card.classList.add('popover-open');
                    } else if (e.key === 'Escape') {
                        card.classList.remove('popover-open');
                    }
                });
            });

            // Close when clicking outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.grade-card')) {
                    gradeCards.forEach(c => c.classList.remove('popover-open'));
                }
            });
        });
    </script>
@endpush

