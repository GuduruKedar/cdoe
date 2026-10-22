@extends('layouts.app')

@section('hideTitle', true)

@section('hidePageSub', true)

@section('content')
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
    </style>
@endpush

