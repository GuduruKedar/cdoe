@extends('layouts.app')

@section('page_title', 'Student Dashboard')
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
            <span class="dash-single-greeting">{{ $greeting }}, <strong class="dash-user-glow">{{ $studentDisplayName }}</strong>!</span>
        </div>
        <div class="dash-single-right">
            <div class="dash-session-chip text-nowrap" style="white-space: nowrap; flex-shrink: 0;">
                <span class="dash-pulse-dot" style="background: #10b981;"></span>
                <span>
                    @if(!empty($isPreview))
                        <span class="text-warning-emphasis fw-bold">Student Role Preview (ID: {{ $student->register_no ?? 'Demo' }})</span>
                        <span class="text-muted mx-2">•</span>
                    @endif
                    Session 2026-2027 • Active Student
                </span>
            </div>
        </div>
    </div>

    {{-- ================= 2. UNIFIED 8-BOX GRID (All Same Size as Raise Ticket) ================= --}}
    <div class="row g-3">

        {{-- Box 1: Enrolled Degree --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('admissions.info') }}" class="service-action-card">
                <div class="service-icon-box blue">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="service-content">
                    <div class="service-title">{{ $studentStats['program'] }}</div>
                    <div class="service-desc text-truncate" title="{{ $studentStats['program_full'] }} • Batch: {{ $studentStats['batch'] }}">
                        {{ $studentStats['program_full'] }} • Batch: {{ $studentStats['batch'] }}
                    </div>
                </div>
                <i class="bi bi-chevron-right service-arrow"></i>
            </a>
        </div>

        {{-- Box 2: Semester & Subjects --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('academics.subjectlist') }}" class="service-action-card">
                <div class="service-icon-box green">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div class="service-content">
                    <div class="service-title">Semester {{ $studentStats['current_sem'] }}</div>
                    <div class="service-desc text-truncate" title="{{ $studentStats['total_subjects'] }} Registered Subjects • {{ $studentStats['status'] }} Student">
                        {{ $studentStats['total_subjects'] }} Registered Subjects • {{ $studentStats['status'] }}
                    </div>
                </div>
                <i class="bi bi-chevron-right service-arrow"></i>
            </a>
        </div>

        {{-- Box 3: Evaluation (SGPA / CGPA) --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('examinations.mmview') }}" class="service-action-card">
                <div class="service-icon-box amber">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div class="service-content">
                    <div class="service-title">
                        @if($studentStats['latest_sgpa'] !== '—')
                            {{ number_format((float)$studentStats['latest_sgpa'], 2) }} SGPA
                        @else
                            Evaluated
                        @endif
                    </div>
                    <div class="service-desc text-truncate" title="Consolidated CGPA: {{ $studentStats['latest_cgpa'] }} • {{ $studentStats['passed'] }} Passed • {{ $studentStats['backlogs'] }} Backlogs">
                        CGPA: {{ $studentStats['latest_cgpa'] }} • {{ $studentStats['passed'] }} Passed • {{ $studentStats['backlogs'] }} Backlogs
                    </div>
                </div>
                <i class="bi bi-chevron-right service-arrow"></i>
            </a>
        </div>

        {{-- Box 4: Support Status --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('visa.ticketraise') }}" class="service-action-card">
                <div class="service-icon-box crimson">
                    <i class="bi bi-headset"></i>
                </div>
                <div class="service-content">
                    <div class="service-title">{{ $studentStats['tickets_count'] }} Support Tickets</div>
                    <div class="service-desc text-truncate" title="{{ $studentStats['open_tickets'] }} Open/In Process • VISA Helpdesk">
                        {{ $studentStats['open_tickets'] }} Open / In Process • VISA Helpdesk
                    </div>
                </div>
                <i class="bi bi-chevron-right service-arrow"></i>
            </a>
        </div>

        {{-- Box 5: Examination Results --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('examinations.mmview') }}" class="service-action-card">
                <div class="service-icon-box blue">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
                <div class="service-content">
                    <div class="service-title">Examination Results</div>
                    <div class="service-desc text-truncate" title="View semester marks memo, SGPA & CGPA grade cards">
                        View semester marks memo, SGPA & CGPA grade cards
                    </div>
                </div>
                <i class="bi bi-chevron-right service-arrow"></i>
            </a>
        </div>

        {{-- Box 6: My Subject List --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('academics.subjectlist') }}" class="service-action-card">
                <div class="service-icon-box green">
                    <i class="bi bi-book-half"></i>
                </div>
                <div class="service-content">
                    <div class="service-title">My Subject List</div>
                    <div class="service-desc text-truncate" title="Curriculum syllabus, course codes & credit details">
                        Curriculum syllabus, course codes & credit details
                    </div>
                </div>
                <i class="bi bi-chevron-right service-arrow"></i>
            </a>
        </div>

        {{-- Box 7: Admission Info --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('admissions.info') }}" class="service-action-card">
                <div class="service-icon-box amber">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div class="service-content">
                    <div class="service-title">Admission Info</div>
                    <div class="service-desc text-truncate" title="Personal details, application number & student identity">
                        Personal details, application number & student identity
                    </div>
                </div>
                <i class="bi bi-chevron-right service-arrow"></i>
            </a>
        </div>

        {{-- Box 8: Raise Ticket (Reference size box) --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('visa.ticketraise') }}" class="service-action-card">
                <div class="service-icon-box crimson">
                    <i class="bi bi-ticket-detailed-fill"></i>
                </div>
                <div class="service-content">
                    <div class="service-title">Raise Ticket</div>
                    <div class="service-desc text-truncate" title="Submit queries for academic, exam, or general support">
                        Submit queries for academic, exam, or general support
                    </div>
                </div>
                <i class="bi bi-chevron-right service-arrow"></i>
            </a>
        </div>

    </div>

</div>
@endsection

@push('styles')
<style>
.cdoe-dashboard-wrap {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* 1. Header */
.dash-single-line-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-card, #e2e8f0);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    flex-wrap: wrap;
    gap: 12px;
}
.dash-single-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-primary, #0f172a);
}
.dash-single-sep {
    color: #94a3b8;
    margin: 0 4px;
}
.dash-single-greeting {
    font-size: 1rem;
    color: var(--text-secondary, #475569);
}
.dash-user-glow {
    color: #0f3b60;
    font-weight: 700;
}
.dash-session-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 5px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #334155;
}
.dash-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

/* 2. Uniform 8 Boxes (All same size as Raise Ticket) */
.service-action-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-card, #e2e8f0);
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    color: inherit;
    min-height: 86px;
    height: 100%;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.service-action-card:hover {
    border-color: #0f3b60;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 59, 96, 0.08);
    color: inherit;
}
.service-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.service-icon-box.blue { background: #eff6ff; color: #1e40af; }
.service-icon-box.green { background: #f0fdf4; color: #15803d; }
.service-icon-box.amber { background: #fffbeb; color: #b45309; }
.service-icon-box.crimson { background: #fef2f2; color: #b91c1c; }

.service-content {
    flex-grow: 1;
    min-width: 0;
}
.service-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.service-desc {
    font-size: 0.76rem;
    color: #64748b;
    line-height: 1.35;
}
.service-arrow {
    color: #94a3b8;
    font-size: 0.9rem;
    transition: transform 0.2s ease;
    flex-shrink: 0;
}
.service-action-card:hover .service-arrow {
    transform: translateX(4px);
    color: #0f3b60;
}
</style>
@endpush
