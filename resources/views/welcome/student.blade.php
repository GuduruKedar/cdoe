@extends('layouts.app')

@section('page_title', 'Student Dashboard')

@section('content')
<div class="cdoe-dashboard-wrap">

    {{-- ================= 1. HERO BANNER ================= --}}
    @php
        $hour = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    @endphp

    <div class="dash-hero-card">
        <div class="dash-hero-content">
            <div class="dash-hero-badge">
                <i class="bi bi-mortarboard-fill me-1"></i> Vignan Online CDOE • Student Portal
            </div>
            <h1 class="dash-hero-title">
                {{ $greeting }}, <span class="dash-hero-name">{{ $userName }}</span>!
            </h1>
            <p class="dash-hero-sub">
                Welcome to your student learning and academic portal. Check your enrolled degree curriculum, track examination results, and access student services.
            </p>

            <div class="dash-hero-actions">
                <a href="{{ route('examinations.stdmarks') }}" class="dash-hero-btn primary">
                    <i class="bi bi-file-earmark-check"></i>
                    <span>My Examination Results</span>
                </a>
            </div>
        </div>

        <div class="dash-hero-aside">
            <div class="dash-hero-emblem-wrap">
                <img src="{{ asset('images/vignan-emblem.png') }}" class="dash-hero-emblem" alt="Vignan Emblem">
            </div>
            <div class="dash-session-pill">
                <span class="dash-pulse-dot"></span>
                <span>Session 2026-2027 • Enrolled</span>
            </div>
        </div>
    </div>

    {{-- ================= 2. STUDENT STATS ================= --}}
    <div class="dash-stat-grid">
        <div class="dash-stat-card blue">
            <div class="dash-stat-header">
                <div class="dash-stat-icon blue"><i class="bi bi-award-fill"></i></div>
                <span class="dash-stat-tag blue">Status</span>
            </div>
            <div class="dash-stat-val">Active</div>
            <div class="dash-stat-label">Enrolment Status</div>
            <div class="dash-stat-footer">
                <i class="bi bi-check-circle-fill text-success me-1"></i> Registered Student Account
            </div>
        </div>

        <div class="dash-stat-card amber">
            <div class="dash-stat-header">
                <div class="dash-stat-icon amber"><i class="bi bi-file-text-fill"></i></div>
                <span class="dash-stat-tag amber">Results</span>
            </div>
            <div class="dash-stat-val">{{ number_format($stats['marks'] ?? 0) }}</div>
            <div class="dash-stat-label">Total Graded Memos</div>
            <div class="dash-stat-footer">
                <i class="bi bi-clock-history me-1"></i> Consolidated Grade Cards
            </div>
        </div>

        <div class="dash-stat-card crimson">
            <div class="dash-stat-header">
                <div class="dash-stat-icon crimson"><i class="bi bi-building"></i></div>
                <span class="dash-stat-tag crimson">Degree</span>
            </div>
            <div class="dash-stat-val">{{ number_format($stats['programs'] ?? 0) }}</div>
            <div class="dash-stat-label">Active Programs</div>
            <div class="dash-stat-footer">
                <i class="bi bi-mortarboard me-1"></i> Online Distance Learning
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.cdoe-dashboard-wrap { display: flex; flex-direction: column; gap: 24px; }
.dash-hero-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 18px;
    padding: 32px 36px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 28px;
    box-shadow: var(--card-shadow);
    backdrop-filter: blur(12px);
    position: relative;
    overflow: hidden;
}
.dash-hero-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--theme-gradient); }
.dash-hero-badge { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 999px; background: var(--theme-primary-light); color: var(--theme-primary); border: 1px solid var(--theme-focus-ring); font-size: 12px; font-weight: 700; margin-bottom: 12px; }
.dash-hero-title { font-size: 28px; font-weight: 800; color: var(--text-primary); margin-bottom: 10px; }
.dash-hero-name { color: var(--theme-primary); }
.dash-hero-sub { color: var(--text-secondary); font-size: 14px; line-height: 1.6; margin-bottom: 22px; max-width: 580px; }
.dash-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.dash-hero-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s ease; }
.dash-hero-btn.primary { background: var(--theme-gradient); color: #fff !important; box-shadow: 0 4px 14px var(--theme-glow); }
.dash-hero-aside { display: flex; flex-direction: column; align-items: center; gap: 14px; flex-shrink: 0; }
.dash-hero-emblem-wrap { width: 90px; height: 90px; border-radius: 20px; background: var(--bg-elevated); border: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: center; padding: 12px; box-shadow: var(--card-shadow); }
.dash-hero-emblem { width: 100%; height: 100%; object-fit: contain; }
.dash-session-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; background: var(--bg-elevated); border: 1px solid var(--border-subtle); border-radius: 999px; font-size: 11.5px; font-weight: 600; color: var(--text-secondary); }
.dash-pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981; }

.dash-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 18px; }
.dash-stat-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 16px; padding: 22px 24px; box-shadow: var(--card-shadow); backdrop-filter: blur(12px); display: flex; flex-direction: column; }
.dash-stat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.dash-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.dash-stat-icon.blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
.dash-stat-icon.amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.dash-stat-icon.crimson { background: rgba(193, 18, 31, 0.12); color: #c1121f; }
.dash-stat-tag { font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; }
.dash-stat-tag.blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.dash-stat-tag.amber { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.dash-stat-tag.crimson { background: rgba(193, 18, 31, 0.1); color: #c1121f; }
.dash-stat-val { font-size: 32px; font-weight: 800; color: var(--text-primary); line-height: 1; margin-bottom: 6px; }
.dash-stat-label { font-size: 13.5px; font-weight: 600; color: var(--text-secondary); margin-bottom: 12px; }
.dash-stat-footer { margin-top: auto; padding-top: 10px; border-top: 1px solid var(--border-subtle); font-size: 11.5px; color: var(--text-muted); }
</style>
@endpush
