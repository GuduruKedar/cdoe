@extends('layouts.app')

@section('page_title', 'Dashboard')
@section('hideTitle', true)
@section('hidePageSub', true)

@section('content')
<div class="cdoe-dashboard-wrap">

    {{-- ================= 1. CLEAN SINGLE-LINE GREETING HEADER ================= --}}
    @php
        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    @endphp

    <div class="dash-single-line-header">
        <div class="dash-single-left">
            <span class="dash-single-title">Dashboard</span>
            <span class="dash-single-sep">::</span>
            <span class="dash-single-greeting">{{ $greeting }}, <strong class="dash-user-glow">{{ $userName }}</strong>!</span>
        </div>
        <div class="dash-single-right">
            <div class="dash-session-chip">
                <span class="dash-pulse-dot" style="background: #10b981;"></span>
                <span>Session 2026-2027 • Active</span>
            </div>
        </div>
    </div>

    {{-- ================= 2. LIVE STATISTICS GRID ================= --}}
    <div class="dash-stat-grid">

        {{-- Students --}}
        <div class="dash-stat-card blue">
            <div class="dash-stat-header">
                <div class="dash-stat-icon blue">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <span class="dash-stat-tag blue">Enrolled</span>
            </div>
            <div class="dash-stat-val">{{ number_format($stats['students'] ?? 0) }}</div>
            <div class="dash-stat-label">Total Registered Students</div>
            <div class="dash-stat-footer">
                <i class="bi bi-check-circle-fill text-success me-1"></i> Active across online degree programs
            </div>
        </div>

        {{-- Faculty & Staff --}}
        <div class="dash-stat-card green">
            <div class="dash-stat-header">
                <div class="dash-stat-icon green">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <span class="dash-stat-tag green">Personnel</span>
            </div>
            <div class="dash-stat-val">{{ number_format($stats['employees'] ?? 0) }}</div>
            <div class="dash-stat-label">Faculty & Staff Members</div>
            <div class="dash-stat-footer">
                <i class="bi bi-shield-fill-check text-info me-1"></i> Academic and administrative roles
            </div>
        </div>

        {{-- Examination Memos --}}
        <div class="dash-stat-card amber">
            <div class="dash-stat-header">
                <div class="dash-stat-icon amber">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
                <span class="dash-stat-tag amber">Evaluated</span>
            </div>
            <div class="dash-stat-val">{{ number_format($stats['marks'] ?? 0) }}</div>
            <div class="dash-stat-label">Exam Marks Records</div>
            <div class="dash-stat-footer">
                <i class="bi bi-graph-up-arrow text-warning me-1"></i> Consolidated semester SGPA & CGPA
            </div>
        </div>

        {{-- Academic Programs --}}
        <div class="dash-stat-card crimson">
            <div class="dash-stat-header">
                <div class="dash-stat-icon crimson">
                    <i class="bi bi-book-half"></i>
                </div>
                <span class="dash-stat-tag crimson">Degrees</span>
            </div>
            <div class="dash-stat-val">{{ number_format($stats['programs'] ?? 0) }}</div>
            <div class="dash-stat-label">Online Programs</div>
            <div class="dash-stat-footer">
                <i class="bi bi-award-fill text-danger me-1"></i> BBA, BCA, MBA & MCA Curriculums
            </div>
        </div>

    </div>

    {{-- ================= 3. ANALYTICAL CHARTS & GRAPHS ================= --}}
    <div class="row g-4 dash-charts-row">

        {{-- Enrolment by Degree Program (Bar Chart) --}}
        <div class="col-lg-7 col-12">
            <div class="dash-card dash-chart-card">
                <div class="dash-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="dash-card-title mb-1">
                            <i class="bi bi-bar-chart-fill me-2 text-primary"></i>Student Enrolment by Degree Program
                        </h4>
                        <div class="text-muted small">Distribution of active students across online programs</div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill font-monospace" style="font-size: 11.5px;">
                        {{ number_format($stats['students'] ?? 0) }} Total Enrolled
                    </span>
                </div>
                <div class="dash-chart-body" style="position: relative; height: 280px;">
                    <canvas id="programChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Academic Evaluation / Grade Distribution (Doughnut Chart) --}}
        <div class="col-lg-5 col-12">
            <div class="dash-card dash-chart-card">
                <div class="dash-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="dash-card-title mb-1">
                            <i class="bi bi-pie-chart-fill me-2 text-warning"></i>Marks & Grade Evaluation
                        </h4>
                        <div class="text-muted small">Semester grade breakdown across evaluated courses</div>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 rounded-pill font-monospace" style="font-size: 11.5px;">
                        {{ $gradeDistribution->sum('total') }} Grades
                    </span>
                </div>
                <div class="dash-chart-body" style="position: relative; height: 280px;">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= 4. CORE MODULES & WORKSPACES ================= --}}
    <div class="dash-section-title">
        <i class="bi bi-grid-fill me-2"></i> Core Management Workspaces
    </div>

    <div class="dash-modules-grid">

        {{-- Admissions Card --}}
        <a href="{{ route('admissions.stdsearch') }}" class="dash-module-card">
            <div class="dash-mod-icon-wrap mod-blue">
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <div class="dash-mod-body">
                <div class="dash-mod-title">Admissions & Enrolments</div>
                <p class="dash-mod-desc">
                    Search and filter enrolled student profiles, inspect ABC / DEB identifiers, and review admission regulations.
                </p>
                <div class="dash-mod-link">
                    <span>Launch Directory</span>
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>

        {{-- Examinations Card --}}
        <a href="{{ route('examinations.stdmarks') }}" class="dash-module-card">
            <div class="dash-mod-icon-wrap mod-amber">
                <i class="bi bi-award-fill"></i>
            </div>
            <div class="dash-mod-body">
                <div class="dash-mod-title">Examinations & Grading</div>
                <p class="dash-mod-desc">
                    Browse semester marks memos, evaluate SGPA & CGPA transcripts, and download student grade report cards.
                </p>
                <div class="dash-mod-link">
                    <span>View Exam Results</span>
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>

        {{-- HRMS Card --}}
        <a href="{{ route('hrms.empsearch') }}" class="dash-module-card">
            <div class="dash-mod-icon-wrap mod-crimson">
                <i class="bi bi-person-gear"></i>
            </div>
            <div class="dash-mod-body">
                <div class="dash-mod-title">Staff & HRMS Directory</div>
                <p class="dash-mod-desc">
                    Manage university faculty directory, configure departments and designations, and register new staff members.
                </p>
                <div class="dash-mod-link">
                    <span>Open Staff Workspace</span>
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>

    </div>

    {{-- ================= 4. RECENT ACTIVITY & QUICK INFO ================= --}}
    <div class="row g-4 mt-1">

        {{-- Recent Students Table --}}
        <div class="col-lg-8 col-12">
            <div class="dash-card">
                <div class="dash-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="dash-card-title mb-1">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Recent Enrolments
                        </h4>
                        <div class="text-muted small">Latest students registered in online degree programs</div>
                    </div>
                    <a href="{{ route('admissions.stdsearch') }}" class="dash-btn-sm">
                        View All <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table ss-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Reg. No</th>
                                <th>Student Name</th>
                                <th>Program</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentStudents ?? [] as $st)
                                <tr>
                                    <td>
                                        <span class="ss-badge-reg font-monospace">{{ $st->register_no }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="ss-avatar-mini" style="width: 28px; height: 28px; font-size: 11px;">
                                                {{ strtoupper(substr($st->student_name ?? 'S', 0, 1)) }}
                                            </div>
                                            <span class="ss-name" style="font-size: 13px;">{{ $st->student_name ?? 'Student' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ss-badge-batch">{{ $st->program_short_name ?? 'CDOE' }}</span>
                                    </td>
                                    <td>
                                        <span class="ss-status-badge ss-status-active">
                                            <span class="ss-dot-pulse"></span>
                                            {{ $st->std_status ?? 'Active' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admissions.stdsearch') }}?q={{ $st->register_no }}" class="ss-view-btn" style="padding: 4px 10px; font-size: 11px;">
                                            <span>Inspect</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No recent enrolment records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Overview Side Card --}}
        <div class="col-lg-4 col-12">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h4 class="dash-card-title mb-1">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Institution Overview
                    </h4>
                    <div class="text-muted small">Centre for Distance & Online Education</div>
                </div>

                <div class="dash-info-list">
                    <div class="dash-info-item">
                        <div class="dash-info-icon"><i class="bi bi-calendar3"></i></div>
                        <div>
                            <div class="dash-info-label">Academic Calendar</div>
                            <div class="dash-info-val">2026 - 2027 Academic Year</div>
                        </div>
                    </div>

                    <div class="dash-info-item">
                        <div class="dash-info-icon"><i class="bi bi-shield-lock-fill"></i></div>
                        <div>
                            <div class="dash-info-label">Authentication System</div>
                            <div class="dash-info-val">Role-Based Access Control (RBAC)</div>
                        </div>
                    </div>

                    <div class="dash-info-item">
                        <div class="dash-info-icon"><i class="bi bi-cloud-check-fill"></i></div>
                        <div>
                            <div class="dash-info-label">System Health</div>
                            <div class="dash-info-val text-success">
                                <span class="dash-pulse-dot" style="background: #10b981; display: inline-block; margin-right: 4px;"></span>
                                All Services Operational
                            </div>
                        </div>
                    </div>

                    <div class="dash-info-item">
                        <div class="dash-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <div class="dash-info-label">Campus Location</div>
                            <div class="dash-info-val">Vadlamudi, Guntur, AP, India</div>
                        </div>
                    </div>
                </div>

                <div class="dash-quick-links mt-4 pt-3 border-top">
                    <a href="{{ route('hrms.empcreation') }}" class="btn btn-sm w-100 dash-action-btn">
                        <i class="bi bi-person-plus-fill me-1"></i> Register New Staff Member
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('styles')
<style>
/* ===================== DASHBOARD STYLES ===================== */
.cdoe-dashboard-wrap {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* 1. Clean Single-Line Header */
.dash-single-line-header {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 14px;
    padding: 14px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: var(--card-shadow);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.dash-single-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.dash-single-title {
    font-size: 19px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.3px;
}
.dash-single-sep {
    font-size: 17px;
    font-weight: 700;
    color: var(--theme-primary);
}
.dash-single-greeting {
    font-size: 15.5px;
    font-weight: 500;
    color: var(--text-secondary);
}
.dash-user-glow {
    color: var(--theme-primary);
    font-weight: 700;
}
.dash-single-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.dash-session-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 14px;
    background: var(--bg-elevated);
    border: 1px solid var(--border-subtle);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
}
.dash-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px #10b981;
    display: inline-block;
}

/* Charts & Graphs */
.dash-charts-row {
    margin-top: -4px;
}
.dash-chart-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.dash-chart-body {
    flex: 1;
    width: 100%;
}

/* 2. Stat Grid */
.dash-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 18px;
}
.dash-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 16px;
    padding: 22px 24px;
    box-shadow: var(--card-shadow);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    display: flex;
    flex-direction: column;
}
.dash-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-shadow-hover);
    border-color: var(--border-hover);
}
.dash-stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.dash-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.dash-stat-icon.blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.dash-stat-icon.green { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.dash-stat-icon.amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.dash-stat-icon.crimson { background: rgba(193, 18, 31, 0.12); color: #c1121f; }

.dash-stat-tag {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 3px 8px;
    border-radius: 6px;
}
.dash-stat-tag.blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.dash-stat-tag.green { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.dash-stat-tag.amber { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.dash-stat-tag.crimson { background: rgba(193, 18, 31, 0.1); color: #c1121f; }

.dash-stat-val {
    font-size: 32px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 6px;
    font-feature-settings: "tnum";
}
.dash-stat-label {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 12px;
}
.dash-stat-footer {
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid var(--border-subtle);
    font-size: 11.5px;
    color: var(--text-muted);
}

/* 3. Modules Grid */
.dash-section-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    margin-top: 6px;
}
.dash-modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
    gap: 20px;
}
.dash-module-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--card-shadow);
    text-decoration: none;
    display: flex;
    gap: 18px;
    transition: all 0.22s ease;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.dash-module-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--card-shadow-hover);
    border-color: var(--theme-primary);
}
.dash-mod-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.dash-mod-icon-wrap.mod-blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.dash-mod-icon-wrap.mod-amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.dash-mod-icon-wrap.mod-crimson { background: rgba(193, 18, 31, 0.12); color: #c1121f; }

.dash-mod-body {
    flex: 1;
    min-width: 0;
}
.dash-mod-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
    transition: color 0.15s;
}
.dash-module-card:hover .dash-mod-title {
    color: var(--theme-primary);
}
.dash-mod-desc {
    font-size: 12.5px;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: 12px;
}
.dash-mod-link {
    font-size: 12px;
    font-weight: 700;
    color: var(--theme-primary);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.dash-module-card:hover .dash-mod-link i {
    transform: translateX(3px);
    transition: transform 0.15s;
}

/* 4. Dash Card & Table */
.dash-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 16px;
    padding: 22px 24px;
    box-shadow: var(--card-shadow);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    height: 100%;
}
.dash-card-header {
    margin-bottom: 18px;
}
.dash-card-title {
    font-size: 16.5px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.dash-btn-sm {
    font-size: 12px;
    font-weight: 600;
    color: var(--theme-primary);
    text-decoration: none;
    padding: 5px 12px;
    border-radius: 8px;
    background: var(--bg-hover-row);
    transition: all 0.15s;
}
.dash-btn-sm:hover {
    background: var(--theme-primary-light);
    color: var(--theme-primary);
}

.dash-info-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.dash-info-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.dash-info-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--bg-elevated);
    border: 1px solid var(--border-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--theme-primary);
    font-size: 14px;
    flex-shrink: 0;
}
.dash-info-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.dash-info-val {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    margin-top: 1px;
}

.dash-action-btn {
    background: var(--theme-gradient);
    color: #fff !important;
    border: none;
    border-radius: 10px;
    padding: 9px 16px;
    font-weight: 600;
    font-size: 13px;
    box-shadow: 0 4px 12px var(--theme-glow);
    transition: all 0.2s;
}
.dash-action-btn:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .dash-single-line-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
    }
    .dash-single-title {
        font-size: 17px;
    }
    .dash-single-greeting {
        font-size: 14px;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.06)';

    // 1. Program Distribution Bar Chart
    const progData = @json($programDistribution ?? []);
    const progLabels = progData.map(item => item.name);
    const progTotals = progData.map(item => item.total);

    const progCtx = document.getElementById('programChart');
    if (progCtx && progLabels.length > 0) {
        new Chart(progCtx, {
            type: 'bar',
            data: {
                labels: progLabels,
                datasets: [{
                    label: 'Enrolled Students',
                    data: progTotals,
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.85)',
                        'rgba(6, 182, 212, 0.85)',
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(239, 68, 68, 0.85)'
                    ],
                    borderColor: [
                        '#6366f1',
                        '#06b6d4',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 1.5,
                    borderRadius: 8,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#0f172a',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return `  ${context.parsed.y} students`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    }
                }
            }
        });
    }

    // 2. Grade Distribution Doughnut Chart
    const gradeData = @json($gradeDistribution ?? []);
    const gradeLabels = gradeData.map(item => 'Grade ' + item.letter_grade);
    const gradeTotals = gradeData.map(item => item.total);

    const gradeCtx = document.getElementById('gradeChart');
    if (gradeCtx && gradeLabels.length > 0) {
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: gradeLabels,
                datasets: [{
                    data: gradeTotals,
                    backgroundColor: [
                        '#ef4444', // F
                        '#06b6d4', // S
                        '#3b82f6', // A
                        '#10b981', // O
                        '#6366f1', // B
                        '#f59e0b', // C
                        '#f97316'  // D
                    ],
                    borderWidth: 2,
                    borderColor: isDark ? '#131d31' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: textColor,
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#0f172a',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return `  ${context.label}: ${context.raw} records`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
