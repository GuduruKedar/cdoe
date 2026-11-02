@extends('layouts.app')

@section('page_title', 'Marks KPI & Performance')

@section('content')
<style>
    :root {
        --ink: var(--text-primary, #0f172a);
        --ink-2: var(--text-secondary, #475569);
        --ink-3: var(--text-muted, #94a3b8);
        --paper: var(--bg-main, #f8fafc);
        --surface: var(--bg-card, #ffffff);
        --line: var(--border-subtle, #e2e8f0);
        --line-2: var(--border-card, #edf0f6);
        --accent: var(--theme-primary, #991b1b);
        --accent-soft: var(--theme-primary-light, rgba(153, 27, 27, 0.1));
        --good: #10b981;
        --good-soft: #ecfdf5;
        --watch: #f59e0b;
        --watch-soft: #fffbeb;
        --risk: #ef4444;
        --risk-soft: #fef2f2;
        --info: #3b82f6;
        --info-soft: #eff6ff;
        --purple: #8b5cf6;
        --purple-soft: #f5f3ff;
    }
    html.dark {
        --ink: #f1f5f9;
        --ink-2: #cbd5e1;
        --ink-3: #94a3b8;
        --surface: #1e293b;
        --line: #334155;
        --line-2: rgba(255, 255, 255, 0.08);
        --good-soft: rgba(16, 185, 129, 0.2);
        --watch-soft: rgba(245, 158, 11, 0.2);
        --risk-soft: rgba(239, 68, 68, 0.2);
        --info-soft: rgba(59, 130, 246, 0.2);
        --purple-soft: rgba(139, 92, 246, 0.2);
        --accent-soft: rgba(220, 38, 38, 0.25);
    }

    .kpi-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 4px 6px 36px;
        color: var(--ink);
        font-variant-numeric: tabular-nums;
    }
    .kpi-container a { color: var(--accent); }
    .kpi-container :focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

    /* ================= 1. EXECUTIVE SUMMARY LINE ================= */
    .exec-line {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--surface);
        border: 1px solid var(--line);
        border-left: 5px solid var(--accent);
        border-radius: 12px;
        padding: 12px 18px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .exec-line:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }
    .exec-score {
        display: flex;
        align-items: baseline;
        flex-shrink: 0;
        background: var(--accent-soft);
        padding: 6px 12px;
        border-radius: 8px;
    }
    .exec-score-num {
        font-size: 26px;
        font-weight: 800;
        line-height: 1;
        color: var(--accent);
    }
    .exec-score-of {
        font-size: 13px;
        color: var(--ink-2);
        margin-left: 3px;
        font-weight: 600;
    }
    .exec-text {
        font-size: 13.5px;
        color: var(--ink);
        line-height: 1.5;
        flex: 1;
    }
    .exec-badge {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 10px;
        border-radius: 20px;
        background: var(--line-2);
        color: var(--ink-2);
        white-space: nowrap;
    }

    /* ================= 2. 4 SAAS ANIMATED STAT CARDS ================= */
    .stat-pills-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 18px;
    }
    .stat-pill {
        background: var(--surface);
        border: 1.5px solid var(--line);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease, box-shadow 0.25s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        animation: cardAppear 0.4s ease-out backwards;
    }
    .stat-pill:nth-child(1) { animation-delay: 0.05s; }
    .stat-pill:nth-child(2) { animation-delay: 0.1s; }
    .stat-pill:nth-child(3) { animation-delay: 0.15s; }
    .stat-pill:nth-child(4) { animation-delay: 0.2s; }

    @keyframes cardAppear {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-pill:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08), 0 4px 8px -2px rgba(0, 0, 0, 0.04);
    }
    .stat-pill.is-active-card {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 2px var(--accent-soft), 0 8px 18px rgba(0, 0, 0, 0.08);
    }

    .stat-pill-cgpa { border-left: 4.5px solid #3b82f6 !important; }
    .stat-pill-pass { border-left: 4.5px solid #10b981 !important; }
    .stat-pill-risk { border-left: 4.5px solid #f59e0b !important; }
    .stat-pill-total { border-left: 4.5px solid #8b5cf6 !important; }

    .stat-pill-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .stat-pill-label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: -0.2px;
        color: var(--ink-2);
        text-transform: uppercase;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .dot-cgpa { background: #3b82f6; box-shadow: 0 0 6px rgba(59, 130, 246, 0.5); }
    .dot-pass { background: #10b981; box-shadow: 0 0 6px rgba(16, 185, 129, 0.5); }
    .dot-risk { background: #f59e0b; }
    .dot-total { background: #8b5cf6; box-shadow: 0 0 6px rgba(139, 92, 246, 0.5); }

    @keyframes pulseWarning {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.5); }
        50% { transform: scale(1.3); box-shadow: 0 0 0 5px rgba(245, 158, 11, 0); }
    }
    .dot-pulsing {
        animation: pulseWarning 1.8s infinite ease-in-out;
    }

    .stat-pill-val-wrap {
        display: flex;
        align-items: baseline;
        gap: 4px;
        margin: 2px 0 6px;
    }
    .stat-pill-val {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.5px;
    }
    .stat-pill-cgpa .stat-pill-val { color: #2563eb; }
    .stat-pill-pass .stat-pill-val { color: #059669; }
    .stat-pill-risk .stat-pill-val { color: #d97706; }
    .stat-pill-total .stat-pill-val { color: #7c3aed; }
    html.dark .stat-pill-cgpa .stat-pill-val { color: #60a5fa; }
    html.dark .stat-pill-pass .stat-pill-val { color: #34d399; }
    html.dark .stat-pill-risk .stat-pill-val { color: #fbbf24; }
    html.dark .stat-pill-total .stat-pill-val { color: #a78bfa; }

    .stat-pill-unit {
        font-size: 13px;
        font-weight: 600;
        color: var(--ink-3);
    }
    .stat-pill-sub {
        font-size: 12px;
        color: var(--ink-3);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }
    .stat-pill-trend {
        font-weight: 600;
        font-size: 11.5px;
    }

    /* ================= 3. COMPACT FILTERS CARD ================= */
    .filters {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 12px 16px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 10px;
        align-items: end;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .filters label { display: block; font-size: 11.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 3px; }
    .filters input, .filters select {
        width: 100%;
        padding: 6px 9px;
        border: 1px solid var(--line);
        border-radius: 6px;
        font: inherit;
        font-size: 13px;
        color: var(--ink);
        background: var(--surface);
        transition: border-color 0.15s ease;
    }
    .filters input:focus, .filters select:focus {
        border-color: var(--accent);
        outline: none;
    }
    .filters .actions { display: flex; gap: 6px; }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid var(--line);
        background: var(--surface);
        color: var(--ink);
        font: inherit;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .btn:hover { border-color: var(--ink-2); background: var(--line-2); }
    .btn.primary { background: var(--accent); border-color: var(--accent); color: #fff; }
    .btn.primary:hover { opacity: 0.92; transform: translateY(-1px); }

    /* ================= 4. VIEW TOGGLE BAR ================= */
    .view-switch-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }
    .view-switch {
        display: inline-flex;
        background: var(--surface);
        border: 1.5px solid var(--line);
        border-radius: 10px;
        padding: 3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .view-btn {
        border: 0;
        background: transparent;
        padding: 8px 18px;
        border-radius: 8px;
        font: inherit;
        font-weight: 700;
        font-size: 13px;
        color: var(--ink-2);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.18s ease;
    }
    .view-btn:hover { color: var(--ink); }
    .view-btn.is-active {
        background: var(--accent);
        color: #fff !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    }
    .view-btn-count {
        background: rgba(0, 0, 0, 0.08);
        padding: 1px 7px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }
    .view-btn.is-active .view-btn-count {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }
    .view-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* ================= 5. OVERVIEW DASHBOARD (DECREASED TO 4 SAAS CHARTS) ================= */
    .overview-charts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }
    .saas-chart-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .saas-chart-card:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    }
    .saas-chart-header {
        padding: 12px 16px;
        border-bottom: 1px solid var(--line-2);
        background: var(--surface);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .saas-chart-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--ink);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }
    .saas-chart-note {
        font-size: 12px;
        color: var(--ink-3);
    }
    .saas-chart-body {
        padding: 14px 16px;
        position: relative;
        height: 290px;
        flex: 1;
    }

    /* Transcript Strip (Semester Progress) */
    .sem-strip-wrap {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow-x: auto;
        display: flex;
        margin-bottom: 18px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .sem-card {
        flex: 1 0 160px;
        padding: 14px 16px;
        border-right: 1px solid var(--line-2);
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .sem-card:last-child { border-right: 0; }
    .sem-card-lbl {
        font-size: 12px;
        color: var(--ink-2);
        display: flex;
        justify-content: space-between;
        font-weight: 600;
    }
    .sem-card-val {
        font-size: 26px;
        font-weight: 800;
        line-height: 1;
        color: var(--ink);
    }
    .sem-card-delta { font-size: 12px; font-weight: 700; }
    .sem-card-sub { font-size: 11.5px; color: var(--ink-3); }

    /* Deep Dive Callout Banner */
    .deep-dive-callout {
        background: var(--surface);
        border: 1.5px dashed var(--line);
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 14px;
    }
    .deep-dive-callout-text h4 { font-size: 14.5px; font-weight: 700; margin: 0 0 2px; color: var(--ink); }
    .deep-dive-callout-text p { font-size: 13px; color: var(--ink-2); margin: 0; }

    /* ================= 6. DETAILED VIEW (DEEP DIVE SUB-TABS) ================= */
    .detailed-nav {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        border-bottom: 1px solid var(--line);
        padding-bottom: 8px;
    }
    .detailed-nav-btn {
        border: 1px solid var(--line);
        background: var(--surface);
        padding: 6px 14px;
        border-radius: 8px;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink-2);
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .detailed-nav-btn:hover { background: var(--line-2); color: var(--ink); }
    .detailed-nav-btn.is-active {
        background: var(--ink);
        border-color: var(--ink);
        color: #fff;
    }
    html.dark .detailed-nav-btn.is-active {
        background: var(--accent);
        border-color: var(--accent);
    }

    .detailed-section {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 16px 18px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        margin-bottom: 16px;
    }

    /* Tables */
    .tbl-wrap {
        overflow-x: auto;
        max-height: 480px;
        overflow-y: auto;
        border: 1px solid var(--line-2);
        border-radius: 8px;
    }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid var(--line-2); white-space: nowrap; }
    th {
        position: sticky;
        top: 0;
        background: var(--surface);
        font-weight: 700;
        color: var(--ink-2);
        cursor: pointer;
        user-select: none;
        z-index: 1;
    }
    html.dark th { background: #1e293b; }
    th[data-sort]::after { content: ""; margin-left: 4px; }
    th.asc::after { content: " ▲"; font-size: 9px; } th.desc::after { content: " ▼"; font-size: 9px; }
    td.num, th.num { text-align: right; }
    tr:hover td { background: var(--line-2); }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 8px; font-size: 11.5px; font-weight: 700; }
    .badge.good { background: var(--good-soft); color: var(--good); }
    .badge.watch { background: var(--watch-soft); color: var(--watch); }
    .badge.risk { background: var(--risk-soft); color: var(--risk); }
    .badge.info { background: var(--info-soft); color: var(--info); }
    .badge.muted { background: var(--line-2); color: var(--ink-2); }
    .badge.reappear { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    .badge.regular { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge.warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .tbl-tools { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 10px; }
    .tbl-tools input, .tbl-tools select {
        padding: 6px 10px;
        border: 1px solid var(--line);
        border-radius: 6px;
        font: inherit;
        font-size: 13px;
        background: var(--surface);
        color: var(--ink);
    }
    .muted { color: var(--ink-3); font-size: 12.5px; }

    /* ================= 7. AI ANALYTICS PANEL ================= */
    .ai-head {
        background: var(--surface);
        border: 1px solid var(--line);
        border-left: 5px solid var(--accent);
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }
    .ai-head h2 { font-size: 20px; margin: 0; font-weight: 700; color: var(--ink); }
    .ai-head p { margin: 4px 0 0; color: var(--ink-2); font-size: 13.5px; max-width: 70ch; }
    .ai-head .badge { background: var(--accent-soft); color: var(--accent); }
    .insight { display: grid; grid-template-columns: 4px 1fr; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--line-2); }
    .insight:last-child { border-bottom: 0; }
    .insight .rail { border-radius: 2px; }
    .insight .t { font-weight: 700; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; color: var(--ink); }
    .insight .x { color: var(--ink-2); margin-top: 2px; max-width: 85ch; font-size: 13px; }
    .check { border: 1px solid var(--line); border-radius: 8px; margin-bottom: 8px; background: var(--surface); }
    .check summary { padding: 10px 12px; cursor: pointer; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; color: var(--ink); font-weight: 600; }
    .check .body { padding: 0 12px 12px; color: var(--ink-2); font-size: 13px; }
    .check code { display: block; font-size: 12px; background: var(--line-2); border-radius: 4px; padding: 3px 8px; margin-top: 4px; white-space: pre-wrap; color: var(--ink); }
    .ask { display: flex; gap: 8px; margin: 6px 0 8px; }
    .ask input { flex: 1; padding: 8px 12px; border: 1px solid var(--line); border-radius: 6px; font: inherit; background: var(--surface); color: var(--ink); }
    .sugg { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
    .sugg button { border: 1px solid var(--line); background: var(--surface); border-radius: 14px; padding: 3px 10px; font: inherit; font-size: 12px; color: var(--ink-2); cursor: pointer; }
    .answer { background: var(--accent-soft); border-radius: 8px; padding: 10px 12px; min-height: 44px; color: var(--ink); }
    .empty { background: var(--surface); border: 1px dashed var(--line); border-radius: 12px; padding: 40px 24px; text-align: center; }
    .empty h2 { margin: 0 0 6px; font-weight: 700; color: var(--ink); }
    .empty p { color: var(--ink-2); margin: 0 0 14px; }
    .notice { padding: 10px 14px; border-radius: 8px; margin-bottom: 14px; border: 1px solid; font-size: 13.5px; }
    .notice.warn { background: var(--watch-soft); border-color: rgba(245, 158, 11, 0.4); color: var(--watch); }
    .notice.err { background: var(--risk-soft); border-color: rgba(239, 68, 68, 0.4); color: var(--risk); }
    .up { color: var(--good); } .down { color: var(--risk); } .flat { color: var(--ink-3); }
    [hidden] { display: none !important; }

    @media (max-width: 1024px) {
        .stat-pills-row { grid-template-columns: repeat(2, 1fr); }
        .overview-charts-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .stat-pills-row { grid-template-columns: 1fr; }
        .exec-line { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="kpi-container">

    {{-- ===================== FILTERS ===================== --}}
    <form class="filters" method="GET" action="{{ url()->current() }}">
        <div>
            <label for="f-regno">Register number</label>
            <input id="f-regno" name="regno" value="{{ $filters['regno'] ?? '' }}" placeholder="e.g. 261BHANU99" autocomplete="off">
        </div>
        <div>
            <label for="f-program">Program</label>
            <select id="f-program" name="program_id">
                <option value="">All programs</option>
                @foreach ($options['programs'] as $p)
                    <option value="{{ $p['id'] }}" @selected(($filters['program_id'] ?? '') == $p['id'])>{{ $p['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-reg">Regulation</label>
            <select id="f-reg" name="regulation">
                <option value="">All</option>
                @foreach ($options['regulations'] as $v)
                    <option value="{{ $v }}" @selected(($filters['regulation'] ?? '') == $v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-year">Year</label>
            <select id="f-year" name="s_year">
                <option value="">All</option>
                @foreach ($options['years'] as $v)
                    <option value="{{ $v }}" @selected(($filters['s_year'] ?? '') == $v)>Year {{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-sem">Semester</label>
            <select id="f-sem" name="s_sem">
                <option value="">All</option>
                @foreach ($options['sems'] as $v)
                    <option value="{{ $v }}" @selected(($filters['s_sem'] ?? '') == $v)>Sem {{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-exam">Exam month</label>
            <select id="f-exam" name="exam">
                <option value="">All exams</option>
                @foreach ($options['exams'] as $v)
                    <option value="{{ $v }}" @selected(($filters['exam'] ?? '') == $v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-gender">Gender</label>
            <select id="f-gender" name="gender">
                <option value="">All</option>
                @foreach ($options['genders'] as $v)
                    <option value="{{ $v }}" @selected(($filters['gender'] ?? '') == $v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="f-type">Attempt type</label>
            <select id="f-type" name="mm_type">
                <option value="">Regular + reappear</option>
                @foreach ($options['types'] as $v)
                    <option value="{{ $v }}" @selected(($filters['mm_type'] ?? '') == $v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="actions">
            <button class="btn primary" type="submit"><i class="bi bi-search"></i> Filter</button>
            <a class="btn" href="{{ url()->current() }}">Clear</a>
        </div>
    </form>

    @if ($error)
        <div class="notice err"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $error }}</div>
    @endif

    @if (!$kpi || !empty($kpi['empty']))
        <div class="empty">
            @if ($kpi && !empty($kpi['empty']))
                <h2>No results match these filters</h2>
                <p>Check the register number or widen the program, semester or exam filters.</p>
            @else
                <h2>Choose what to analyse</h2>
                <p>Enter a register number for one student's report, or pick a program, regulation or exam for a cohort view.</p>
                <a class="btn primary" href="{{ url()->current() }}?all=1"><i class="bi bi-bar-chart-line-fill me-1"></i> Analyse all results</a>
            @endif
        </div>
    @else

        @if (!empty($kpi['meta']['truncated']))
            <div class="notice warn"><i class="bi bi-info-circle-fill me-1"></i> Only the first {{ number_format($kpi['meta']['row_limit']) }} result rows were analysed. Narrow filters for complete figures.</div>
        @endif

        {{-- ================= 1. EXECUTIVE HEADLINE LINE ================= --}}
        <div class="exec-line">
            <div class="exec-score" title="Overall average CGPA">
                <span class="exec-score-num" id="execCgpaVal">{{ number_format($kpi['summary']['cgpa'] ?? 0, 2) }}</span>
                <span class="exec-score-of">/ 10</span>
            </div>
            <div class="exec-text mb-0">
                <strong id="execHeadlineTitle">Overall Cohort Performance.</strong>
                <span id="scopeLine">Results, grades, SGPA/CGPA and data checks from examinations.marks_cgpa</span>
            </div>
            <span class="exec-badge" id="execScopeBadge">Cohort Analytics</span>
        </div>

        {{-- ================= 2. 4 SAAS ANIMATED STAT CARDS ================= --}}
        <div class="stat-pills-row">
            {{-- Card 1: CGPA --}}
            <div class="stat-pill stat-pill-cgpa" id="cardCgpa" title="Click to focus Grade Distribution">
                <div class="stat-pill-header">
                    <div class="stat-pill-label"><span class="status-dot dot-cgpa"></span><span id="card1Label">Cohort CGPA</span></div>
                    <i class="bi bi-mortarboard-fill text-primary"></i>
                </div>
                <div class="stat-pill-val-wrap">
                    <span class="stat-pill-val" id="card1Val">{{ number_format($kpi['summary']['cgpa'] ?? 0, 2) }}</span>
                    <span class="stat-pill-unit">/ 10</span>
                </div>
                <div class="stat-pill-sub">
                    <span id="card1Sub">Median {{ number_format($kpi['summary']['cgpa_median'] ?? 0, 2) }}</span>
                    <span class="stat-pill-trend text-primary">Score</span>
                </div>
            </div>

            {{-- Card 2: Pass Rate --}}
            <div class="stat-pill stat-pill-pass" id="cardPass" title="Click to open Detailed View">
                <div class="stat-pill-header">
                    <div class="stat-pill-label"><span class="status-dot dot-pass"></span><span>Pass Rate</span></div>
                    <i class="bi bi-check-circle-fill text-success"></i>
                </div>
                <div class="stat-pill-val-wrap">
                    <span class="stat-pill-val" id="card2Val">{{ $kpi['summary']['pass_pct'] ?? 0 }}%</span>
                </div>
                <div class="stat-pill-sub">
                    <span id="card2Sub">{{ $kpi['summary']['all_clear_pct'] ?? 0 }}% cleared all</span>
                    <span class="stat-pill-trend text-success">Clearance</span>
                </div>
            </div>

            {{-- Card 3: At Risk & Backlogs --}}
            <div class="stat-pill stat-pill-risk" id="cardRisk" title="Click to view At-Risk Roster in Detailed View">
                <div class="stat-pill-header">
                    <div class="stat-pill-label"><span class="status-dot dot-risk dot-pulsing"></span><span>At-Risk / Backlogs</span></div>
                    <i class="bi bi-exclamation-octagon-fill text-warning"></i>
                </div>
                <div class="stat-pill-val-wrap">
                    <span class="stat-pill-val" id="card3Val">{{ $kpi['summary']['at_risk_students'] ?? 0 }}</span>
                    <span class="stat-pill-unit">students</span>
                </div>
                <div class="stat-pill-sub">
                    <span id="card3Sub">{{ $kpi['summary']['backlogs'] ?? 0 }} pending subject(s)</span>
                    <span class="stat-pill-trend text-warning">Needs Support</span>
                </div>
            </div>

            {{-- Card 4: Total Evaluated --}}
            <div class="stat-pill stat-pill-total" id="cardTotal" title="Click to view All Students in Detailed View">
                <div class="stat-pill-header">
                    <div class="stat-pill-label"><span class="status-dot dot-total"></span><span>Total Evaluated</span></div>
                    <i class="bi bi-people-fill text-info"></i>
                </div>
                <div class="stat-pill-val-wrap">
                    <span class="stat-pill-val" id="card4Val">{{ number_format($kpi['summary']['students'] ?? 0) }}</span>
                    <span class="stat-pill-unit">students</span>
                </div>
                <div class="stat-pill-sub">
                    <span id="card4Sub">{{ number_format($kpi['summary']['records'] ?? 0) }} results • {{ $kpi['summary']['sessions'] ?? 0 }} exams</span>
                    <span class="stat-pill-trend text-info">Cohort</span>
                </div>
            </div>
        </div>

        {{-- ================= 3. VIEW TOGGLE BAR ================= --}}
        <div class="view-switch-row">
            <div class="view-switch" role="tablist">
                <button type="button" class="view-btn is-active" id="btn-overview" role="tab" aria-selected="true">
                    <i class="bi bi-speedometer2"></i> SaaS Dashboard
                </button>
                <button type="button" class="view-btn" id="btn-detailed" role="tab" aria-selected="false">
                    <i class="bi bi-table"></i> Detailed View
                    <span class="view-btn-count">{{ number_format($kpi['summary']['students'] ?? 0) }}</span>
                </button>
                <button type="button" class="view-btn" id="btn-report" role="tab" aria-selected="false">
                    <i class="bi bi-file-earmark-medical"></i> Report
                    @if (!empty($kpi['reappear_report']))
                        <span class="view-btn-count" style="background:#dc2626;color:#fff">{{ number_format(count($kpi['reappear_report'])) }}</span>
                    @endif
                </button>
                <button type="button" class="view-btn" id="btn-ai" role="tab" aria-selected="false">
                    <i class="bi bi-stars"></i> AI Perceptions
                    @if (!empty($kpi['summary']['quality_issues']))
                        <span class="view-btn-count" style="background:#ef4444;color:#fff">{{ $kpi['summary']['quality_issues'] }}</span>
                    @endif
                </button>
            </div>
            <div class="view-actions">
                <button type="button" class="btn" id="btn-reappear-csv" title="Export Active Reappear/Backlog CSV"><i class="bi bi-file-earmark-arrow-down text-danger"></i> Reappear CSV</button>
                <button type="button" class="btn text-danger fw-bold" id="btn-reappear-pdf" title="Download Active Reappear/Backlog PDF"><i class="bi bi-file-earmark-pdf-fill"></i> Reappear PDF</button>
                <button type="button" class="btn" id="btn-csv"><i class="bi bi-download"></i> Records CSV</button>
                <button type="button" class="btn" id="btn-subcsv"><i class="bi bi-file-earmark-spreadsheet"></i> Subjects CSV</button>
                <button type="button" class="btn" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
            </div>
        </div>

        {{-- ================= 4. VIEW 1: SAAS OVERVIEW (4 KEY CHARTS) ================= --}}
        <main id="panel-overview">
            {{-- Semester progress strip --}}
            <div class="sem-strip-wrap" id="semStrip"></div>

            {{-- 4 Core SaaS Chart Cards Grid --}}
            <div class="overview-charts-grid">
                {{-- Chart 1: Grade Quality Distribution --}}
                <div class="saas-chart-card" id="anchorGrades">
                    <div class="saas-chart-header">
                        <h3 class="saas-chart-title"><i class="bi bi-bar-chart-fill text-primary"></i> Grade Distribution & Performance</h3>
                        <span class="saas-chart-note">Letter grades count & share</span>
                    </div>
                    <div class="saas-chart-body"><canvas id="cGrades"></canvas></div>
                </div>

                {{-- Chart 2: Semester SGPA & CGPA Growth --}}
                <div class="saas-chart-card">
                    <div class="saas-chart-header">
                        <h3 class="saas-chart-title"><i class="bi bi-graph-up text-success"></i> Semester SGPA & CGPA Growth</h3>
                        <span class="saas-chart-note">Progress trajectory over semesters</span>
                    </div>
                    <div class="saas-chart-body"><canvas id="cSem"></canvas></div>
                </div>

                {{-- Chart 3: Subject Performance Spectrum --}}
                <div class="saas-chart-card">
                    <div class="saas-chart-header">
                        <h3 class="saas-chart-title"><i class="bi bi-award-fill text-warning"></i> Subject Marks & Difficulty Spectrum</h3>
                        <span class="saas-chart-note">Average marks sorted high to low</span>
                    </div>
                    <div class="saas-chart-body"><canvas id="cSubjects"></canvas></div>
                </div>

                {{-- Chart 4: Internal FA vs External SA Balance --}}
                <div class="saas-chart-card">
                    <div class="saas-chart-header">
                        <h3 class="saas-chart-title"><i class="bi bi-pie-chart-fill text-info"></i> Internal (FA) vs External (SA) Balance</h3>
                        <span class="saas-chart-note">Component % & evaluation gap</span>
                    </div>
                    <div class="saas-chart-body"><canvas id="cFaSa"></canvas></div>
                </div>
            </div>

            {{-- Deep Dive Banner --}}
            <div class="deep-dive-callout">
                <div class="deep-dive-callout-text">
                    <h4><i class="bi bi-database-fill-check text-primary me-2"></i>Need Student-by-Student Marks, Backlog Lists or Raw Rows?</h4>
                    <p>Access the detailed student roster, subject matrix, exam reappear tracking, and complete record exports.</p>
                </div>
                <button type="button" class="btn primary" id="btnGoDetailed">
                    <i class="bi bi-arrow-right-circle-fill me-1"></i> Switch to Detailed View
                </button>
            </div>
        </main>

        {{-- ================= 5. VIEW 2: DETAILED VIEW (DEEP DIVE SUB-TABS) ================= --}}
        <section id="panel-detailed" hidden>
            <div class="detailed-nav">
                <button type="button" class="detailed-nav-btn is-active" data-sec="secStudents"><i class="bi bi-people me-1"></i> Students Roster</button>
                <button type="button" class="detailed-nav-btn" data-sec="secSubjects"><i class="bi bi-journal-text me-1"></i> Subject Analysis Matrix</button>
                <button type="button" class="detailed-nav-btn" data-sec="secSemesters"><i class="bi bi-calendar3 me-1"></i> Semester & Exam Summary</button>
                <button type="button" class="detailed-nav-btn" data-sec="secReappear"><i class="bi bi-arrow-repeat me-1"></i> Reappear Tracking</button>
                <button type="button" class="detailed-nav-btn" data-sec="secRecords"><i class="bi bi-card-list me-1"></i> Result Records</button>
            </div>

            {{-- Section A: Students Roster --}}
            <div class="detailed-section" id="secStudents">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h3 class="h6 fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Student Performance Roster</h3>
                        <p class="small text-muted mb-0">Click any register number to open that student's comprehensive performance report.</p>
                    </div>
                    <div class="tbl-tools mb-0">
                        <div class="btn-group btn-group-sm" id="segStudents">
                            <button type="button" class="btn btn-outline-secondary" data-v="top">Top 10</button>
                            <button type="button" class="btn btn-outline-secondary" data-v="bottom">Bottom 10</button>
                            <button type="button" class="btn btn-outline-secondary" data-v="risk">At Risk</button>
                            <button type="button" class="btn btn-outline-secondary active" data-v="all">All Students</button>
                        </div>
                        <input type="search" id="stuSearch" placeholder="Search register number..." aria-label="Search students">
                    </div>
                </div>
                <div class="tbl-wrap"><table id="tStu"></table></div>
            </div>

            {{-- Section B: Subject Analysis Matrix --}}
            <div class="detailed-section d-none" id="secSubjects">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h3 class="h6 fw-bold mb-1"><i class="bi bi-journal-check text-warning me-2"></i>Subject Performance Analysis</h3>
                        <p class="small text-muted mb-0">Gap = internal % minus external %. Gaps exceeding <span id="gapLbl"></span> points are flagged.</p>
                    </div>
                </div>
                <div class="tbl-wrap"><table id="tSub"></table></div>
            </div>

            {{-- Section C: Semester & Exam Breakdown --}}
            <div class="detailed-section d-none" id="secSemesters">
                <div class="mb-3">
                    <h3 class="h6 fw-bold mb-1"><i class="bi bi-calendar-event text-success me-2"></i>Semester Summary Breakdown</h3>
                    <p class="small text-muted mb-0">Click column headers to sort by SGPA, pass rate, or average marks.</p>
                </div>
                <div class="tbl-wrap mb-4"><table id="tSem"></table></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white dark:bg-slate-800">
                            <h4 class="small fw-bold mb-2">Exam Sessions Breakdown</h4>
                            <div style="height:220px"><canvas id="cSessions"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white dark:bg-slate-800">
                            <h4 class="small fw-bold mb-2">Earned vs Registered Credits</h4>
                            <div style="height:220px"><canvas id="cCredits"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section D: Reappear Outcomes --}}
            <div class="detailed-section d-none" id="secReappear">
                <div class="mb-3">
                    <h3 class="h6 fw-bold mb-1"><i class="bi bi-arrow-repeat text-danger me-2"></i>Reappear & Improvement Outcome Analysis</h3>
                    <p class="small text-muted mb-0" id="reapLine">Evaluates attempt effectiveness and score changes across reappear sessions.</p>
                </div>
                <div class="tbl-wrap mb-3"><table id="tReap"></table></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white dark:bg-slate-800">
                            <h4 class="small fw-bold mb-2">Before vs After Reappear Marks %</h4>
                            <div style="height:220px"><canvas id="cReappear"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white dark:bg-slate-800">
                            <h4 class="small fw-bold mb-2">Reappear Attempt Outcomes</h4>
                            <div style="height:220px"><canvas id="cReapOutcome"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section E: Complete Result Records --}}
            <div class="detailed-section d-none" id="secRecords">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h3 class="h6 fw-bold mb-1"><i class="bi bi-card-checklist text-info me-2"></i>Raw Result Records</h3>
                        <p class="small text-muted mb-0">Loaded examination marks rows. Counted flag determines SGPA/CGPA calculation.</p>
                    </div>
                    <div class="tbl-tools mb-0">
                        <input type="search" id="recSearch" placeholder="Search regno, subject or exam..." style="min-width:240px">
                        <select id="recType"><option value="">All attempts</option><option value="counted">Counted only</option><option value="reappear">Reappear only</option><option value="fail">Not cleared</option></select>
                        <span class="muted" id="recCount"></span>
                    </div>
                </div>
                <div class="tbl-wrap"><table id="tRec"></table></div>
            </div>
        </section>

        {{-- ================= 6. VIEW: REAPPEAR REPORT ================= --}}
        <section id="panel-report" hidden>
            <div class="detailed-section mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1" style="color: #c1121f;">
                            <i class="bi bi-file-earmark-medical-fill me-2"></i>REAPPEAR Report
                        </h2>
                        <p class="small text-muted mb-0">
                            Active backlogs requiring reappear examinations. Students who previously failed but subsequently passed the subject are automatically resolved and excluded.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExportReapCsv">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                        </button>
                        <button type="button" class="btn btn-sm btn-danger text-white" id="btnExportReapPdf" style="background: #c1121f; border-color: #c1121f;">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnPrintReap">
                            <i class="bi bi-printer me-1"></i> Print / Preview
                        </button>
                    </div>
                </div>

                {{-- Filter & Search Toolbar --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 p-2 bg-light dark:bg-slate-800 rounded border">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <input type="search" id="reapSearch" class="form-control form-control-sm" placeholder="Search Register No, Student Name, Subject Code..." style="min-width: 270px;">
                        <select id="reapTypeFilter" class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Types</option>
                            <option value="REGULAR">REGULAR</option>
                            <option value="REAPPEAR">REAPPEAR</option>
                        </select>
                        <select id="reapSessionFilter" class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Exam Sessions</option>
                        </select>
                    </div>
                    <div class="small fw-bold text-muted" id="reapCountBadge">
                        Loading report...
                    </div>
                </div>

                {{-- Reappear Report Table --}}
                <div class="tbl-wrap">
                    <table id="tReappearReport"></table>
                </div>
            </div>
        </section>

        {{-- ================= 7. VIEW 3: AI ANALYTICS & AUDIT ================= --}}
        <section id="panel-ai" hidden>
            <div class="ai-head">
                <div>
                    <h2><i class="bi bi-stars text-warning me-2"></i>AI Perceptions & Statistical Insights</h2>
                    <p>Automated statistical findings, data integrity checks, and projections generated on your server with rules.</p>
                </div>
                <span class="badge">Server-side Analytics</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="detailed-section mb-3">
                        <h3 class="h6 fw-bold mb-2"><i class="bi bi-lightbulb-fill text-warning me-2"></i>Key Insights</h3>
                        <div id="insights"></div>
                    </div>
                    <div class="detailed-section">
                        <h3 class="h6 fw-bold mb-2"><i class="bi bi-shield-check text-success me-2"></i>Data Integrity Checks</h3>
                        <div id="checks"></div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="detailed-section">
                        <h3 class="h6 fw-bold mb-2"><i class="bi bi-chat-square-dots-fill text-primary me-2"></i>Ask Performance Assistant</h3>
                        <div class="ask">
                            <input type="text" id="askInput" placeholder="e.g. Which subject is weakest? Who is at risk?">
                            <button type="button" class="btn primary" id="askBtn">Ask</button>
                        </div>
                        <div class="sugg" id="sugg"></div>
                        <div class="answer" id="answer">Answers appear here instantly.</div>
                    </div>
                </div>
            </div>
        </section>

    @endif

@if ($kpi && empty($kpi['empty']))
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
    <script>window.MARKS_KPI = @json($kpi);</script>
    @verbatim
        <script>
            (function () {
                'use strict';
                const K = window.MARKS_KPI;
                if (!K || K.empty) return;
                let renderReap = () => {};

                // ---------- helpers ----------
                const $ = (s) => document.querySelector(s);
                const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                const isNum = (v) => v !== null && v !== undefined && v !== '' && !isNaN(v);
                const n = (v, d = 1) => (isNum(v) ? Number(v).toFixed(d) : '–');
                const pc = (v, d = 1) => (isNum(v) ? Number(v).toFixed(d) + '%' : '–');
                const root = getComputedStyle(document.documentElement);
                const isDark = document.documentElement.classList.contains('dark');
                const V = (name, fallback) => root.getPropertyValue(name).trim() || fallback;
                const C = {
                    ink: isDark ? '#f1f5f9' : (V('--ink', '#0f172a')),
                    ink2: isDark ? '#cbd5e1' : (V('--ink-2', '#475569')),
                    ink3: isDark ? '#94a3b8' : (V('--ink-3', '#94a3b8')),
                    line: isDark ? '#334155' : (V('--line', '#e2e8f0')),
                    line2: isDark ? 'rgba(255,255,255,0.08)' : (V('--line-2', '#edf0f6')),
                    accent: V('--accent', '#991b1b'),
                    good: '#10b981',
                    watch: '#f59e0b',
                    risk: '#ef4444',
                };
                const GRADE_COL = { O: '#0B5D4B', S: '#0E7C66', 'A+': '#10b981', A: '#34d399', 'B+': '#3b82f6', B: '#60a5fa', C: '#f59e0b', D: '#ea580c', E: '#c2410c', P: '#6b7280', F: '#ef4444', AB: '#991b1b' };
                const gradeCol = (g) => GRADE_COL[g] || '#8A93AD';
                const alpha = (hex, a) => { const h = hex.replace('#', ''); const i = parseInt(h, 16); return `rgba(${(i >> 16) & 255},${(i >> 8) & 255},${i & 255},${a})`; };

                const S = K.summary, M = K.meta, isStudent = M.mode === 'student';

                Chart.defaults.font.family = '"Segoe UI", Roboto, -apple-system, BlinkMacSystemFont, Arial, sans-serif';
                Chart.defaults.font.size = 12;
                Chart.defaults.color = C.ink2;
                Chart.defaults.borderColor = C.line2;
                Chart.defaults.plugins.legend.labels.boxWidth = 12;
                Chart.defaults.plugins.tooltip.backgroundColor = C.ink;
                Chart.defaults.maintainAspectRatio = false;

                const charts = {};
                const mk = (id, cfg) => { const el = document.getElementById(id); if (!el) return; if (charts[id]) charts[id].destroy(); charts[id] = new Chart(el, cfg); };
                const pctAxis = (title) => ({ min: 0, max: 100, title: { display: !!title, text: title }, ticks: { callback: (v) => v + '%' } });

                // Update Executive Headline & Cards
                if (isStudent) {
                    const p = M.student;
                    $('#execHeadlineTitle').textContent = `Student Standing: ${p.regno}`;
                    $('#scopeLine').textContent = `${p.program} • Regulation ${p.regulation} • ${p.division || 'Regular'}`;
                    $('#execScopeBadge').textContent = 'Student Report';
                    $('#card1Label').textContent = 'Student CGPA';
                    $('#card1Sub').textContent = p.division || 'Registered';
                    $('#card3Sub').textContent = `${S.backlogs} backlog subject(s)`;
                    $('#card4Val').textContent = S.records;
                    $('#card4Sub').textContent = `${S.subjects} subjects • ${S.semesters} semesters`;
                } else {
                    $('#execHeadlineTitle').textContent = `Cohort Overview: ${S.students} Students Evaluated.`;
                    $('#scopeLine').textContent = `${S.students} Students • ${S.records} Exam Marks Rows • Average CGPA ${n(S.cgpa, 2)} • Pass Rate ${pc(S.pass_pct)}`;
                    $('#execScopeBadge').textContent = 'Cohort Analytics';
                }

                // Render Semester Strip
                (function renderSemStrip() {
                    const semWrap = $('#semStrip');
                    if (!semWrap) return;
                    semWrap.innerHTML = K.semesters.map((s) => {
                        const d = s.delta;
                        const dHtml = isNum(d) ? `<div class="sem-card-delta ${d > 0 ? 'text-success' : d < 0 ? 'text-danger' : 'text-muted'}">${d > 0 ? '▲ +' : d < 0 ? '▼ ' : '— '}${n(Math.abs(d), 2)}</div>` : '';
                        return `<div class="sem-card">
                            <div class="sem-card-lbl"><span>${esc(s.label)}</span><span class="text-muted">${pc(s.avg_pct)}</span></div>
                            <div class="sem-card-val">${n(s.sgpa, 2)}</div>
                            ${dHtml}
                            <div class="sem-card-sub">${s.pass_pct !== undefined ? `Pass: ${pc(s.pass_pct)}` : 'SGPA'}</div>
                        </div>`;
                    }).join('');
                })();

                // ================= PRIMARY SAAS CHARTS =================
                function initOverviewCharts() {
                    const G = K.grades;
                    // 1. Grade Quality Distribution
                    mk('cGrades', {
                        type: 'bar',
                        data: {
                            labels: G.map((g) => g.grade),
                            datasets: [{
                                label: 'Results Count',
                                data: G.map((g) => g.count),
                                backgroundColor: G.map((g) => gradeCol(g.grade)),
                                borderRadius: 6
                            }]
                        },
                        options: {
                            animation: { duration: 750, easing: 'easeOutQuart' },
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { afterLabel: (c) => `Share: ${G[c.dataIndex].share}% (Avg GP: ${G[c.dataIndex].avg_gp})` } }
                            },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                        }
                    });

                    // 2. Semester Growth Trajectory
                    const Sm = K.semesters;
                    mk('cSem', {
                        data: {
                            labels: Sm.map((s) => s.label),
                            datasets: [
                                { type: 'line', label: 'SGPA (recomputed)', data: Sm.map((s) => s.sgpa), borderColor: '#2563eb', backgroundColor: alpha('#2563eb', 0.1), fill: true, tension: 0.3, yAxisID: 'g', pointRadius: 5 },
                                { type: 'line', label: 'CGPA (running)', data: Sm.map((s) => s.cgpa_running), borderColor: '#10b981', backgroundColor: '#10b981', tension: 0.3, yAxisID: 'g', pointRadius: 4 },
                                { type: 'bar', label: 'Avg Marks %', data: Sm.map((s) => s.avg_pct), backgroundColor: alpha(C.ink3, 0.18), borderRadius: 4, yAxisID: 'p' },
                            ],
                        },
                        options: {
                            animation: { duration: 750 },
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                g: { position: 'left', min: 0, max: 10, title: { display: true, text: 'Grade points' } },
                                p: { position: 'right', ...pctAxis('Marks %'), grid: { display: false } }
                            }
                        },
                    });

                    // 3. Subject Performance Spectrum
                    const topSubs = K.subjects.slice(0, 10);
                    mk('cSubjects', {
                        type: 'bar',
                        data: {
                            labels: topSubs.map((s) => s.subcode),
                            datasets: [{
                                label: 'Average Marks %',
                                data: topSubs.map((s) => s.avg_pct),
                                backgroundColor: topSubs.map((s) => s.pass_pct >= 90 ? '#10b981' : s.pass_pct >= 75 ? '#f59e0b' : '#ef4444'),
                                borderRadius: 5
                            }]
                        },
                        options: {
                            animation: { duration: 750 },
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { afterLabel: (c) => `Pass rate: ${pc(topSubs[c.dataIndex].pass_pct)} (${topSubs[c.dataIndex].results} results)` } }
                            },
                            scales: { y: pctAxis() }
                        }
                    });

                    // 4. FA vs SA Component Balance
                    mk('cFaSa', {
                        type: 'bar',
                        data: {
                            labels: topSubs.map((s) => s.subcode),
                            datasets: [
                                { label: 'Internal (FA) %', data: topSubs.map((s) => s.fa_pct), backgroundColor: '#3b82f6', borderRadius: 4 },
                                { label: 'External (SA) %', data: topSubs.map((s) => s.sa_pct), backgroundColor: '#8b5cf6', borderRadius: 4 },
                            ]
                        },
                        options: {
                            animation: { duration: 750 },
                            scales: { y: pctAxis() },
                            plugins: { legend: { position: 'top' } }
                        }
                    });
                }

                // ================= DETAILED VIEW CHARTS =================
                function initDetailedCharts() {
                    const Se = K.sessions;
                    mk('cSessions', {
                        data: {
                            labels: Se.map((s) => s.exam),
                            datasets: [
                                { type: 'bar', label: 'Regular', data: Se.map((s) => s.regular), backgroundColor: '#3b82f6', stack: 'n', borderRadius: 3 },
                                { type: 'bar', label: 'Reappear', data: Se.map((s) => s.reappear), backgroundColor: '#f59e0b', stack: 'n', borderRadius: 3 },
                            ],
                        },
                        options: { scales: { y: { beginAtZero: true } } }
                    });

                    const Sm = K.semesters;
                    mk('cCredits', {
                        type: 'bar',
                        data: {
                            labels: Sm.map((s) => s.label),
                            datasets: [
                                { label: 'Credits Earned', data: Sm.map((s) => s.credits_earned_avg), backgroundColor: '#10b981', borderRadius: 3 },
                                { label: 'Registered', data: Sm.map((s) => s.credits_reg_avg), backgroundColor: alpha('#94a3b8', 0.25), borderRadius: 3 },
                            ]
                        },
                        options: { scales: { y: { beginAtZero: true } } }
                    });

                    const R = K.reappear.rows.slice(0, 15);
                    if (R.length) {
                        mk('cReappear', {
                            type: 'bar',
                            data: {
                                labels: R.map((r) => `${r.regno.slice(-6)} ${r.subcode}`),
                                datasets: [
                                    { label: 'Earlier %', data: R.map((r) => r.before_pct), backgroundColor: '#94a3b8', borderRadius: 3 },
                                    { label: 'Reappear %', data: R.map((r) => r.after_pct), backgroundColor: '#3b82f6', borderRadius: 3 },
                                ]
                            },
                            options: { scales: { y: pctAxis() } }
                        });
                        const outcomes = {};
                        K.reappear.rows.forEach(r => outcomes[r.kind] = (outcomes[r.kind] || 0) + 1);
                        mk('cReapOutcome', {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(outcomes),
                                datasets: [{ data: Object.values(outcomes), backgroundColor: ['#10b981', '#3b82f6', '#ef4444', '#f59e0b'] }]
                            },
                            options: { cutout: '60%' }
                        });
                    }
                }

                // ================= TABLES =================
                function initTables() {
                    table('tSem', [
                        { k: 'label', t: 'Semester' }, { k: 'students', t: 'Students', num: 1 }, { k: 'subject_results', t: 'Results', num: 1 },
                        { k: 'sgpa', t: 'SGPA (recomputed)', num: 1, f: (v) => n(v, 2) },
                        { k: 'sgpa_reported', t: 'SGPA (stored)', num: 1, f: (v, r) => (isNum(v) && Math.abs(v - r.sgpa) > 0.05 ? badge(n(v, 2), 'risk') : n(v, 2)) },
                        { k: 'cgpa_running', t: 'CGPA Running', num: 1, f: (v) => n(v, 2) },
                        { k: 'avg_pct', t: 'Avg %', num: 1, f: (v) => pc(v) }, { k: 'fa_pct', t: 'FA %', num: 1, f: (v) => pc(v) }, { k: 'sa_pct', t: 'SA %', num: 1, f: (v) => pc(v) },
                        { k: 'pass_pct', t: 'Pass %', num: 1, f: (v) => badge(pc(v), v >= 95 ? 'good' : v >= 80 ? 'watch' : 'risk') },
                        { k: 'credits_earned_avg', t: 'Credits Earned', num: 1, f: (v, r) => `${n(v, 0)} / ${n(r.credits_reg_avg, 0)}` },
                    ], K.semesters, { sortKey: 'label', dir: 1 });

                    table('tSub', [
                        { k: 'subcode', t: 'Subject' }, { k: 'semesters', t: 'Semester' }, { k: 'results', t: 'Results', num: 1 }, { k: 'attempts', t: 'Attempts', num: 1 },
                        { k: 'reappears', t: 'Reappears', num: 1 }, { k: 'credits', t: 'Credits', num: 1 },
                        { k: 'avg_pct', t: 'Avg %', num: 1, f: (v) => pc(v) }, { k: 'min_pct', t: 'Min %', num: 1, f: (v) => pc(v) }, { k: 'max_pct', t: 'Max %', num: 1, f: (v) => pc(v) },
                        { k: 'gap', t: 'Gap', num: 1, f: (v) => (isNum(v) && v >= M.gap_alert ? badge(n(v), 'risk') : isNum(v) && v >= 10 ? badge(n(v), 'watch') : n(v)) },
                        { k: 'pass_pct', t: 'Pass %', num: 1, f: (v) => badge(pc(v), v >= 95 ? 'good' : v >= 80 ? 'watch' : 'risk') },
                    ], K.subjects, { sortKey: 'avg_pct' });

                    table('tReap', [
                        { k: 'regno', t: 'Register No' }, { k: 'subcode', t: 'Subject' }, { k: 'semester', t: 'Semester' },
                        { k: 'before_exam', t: 'Earlier Exam' }, { k: 'before_pct', t: 'Earlier %', num: 1, f: (v) => pc(v) },
                        { k: 'after_exam', t: 'Reappear Exam' }, { k: 'after_pct', t: 'Reappear %', num: 1, f: (v) => pc(v) },
                        { k: 'gain', t: 'Change', num: 1, f: (v) => `<span class="${v > 0 ? 'up fw-bold' : v < 0 ? 'down fw-bold' : 'flat'}">${v > 0 ? '+' : ''}${n(v)}</span>` },
                        { k: 'kind', t: 'Outcome', f: (v) => badge(v, v === 'Backlog cleared' ? 'good' : v === 'Still pending' ? 'risk' : 'info') },
                    ], K.reappear.rows, { sortKey: 'regno', dir: 1 });

                    if (!isStudent && K.students) {
                        const stuCols = [
                            { k: 'regno', t: 'Register No', f: (v) => `<a href="${location.pathname}?regno=${encodeURIComponent(v)}" class="fw-bold">${esc(v)}</a>` },
                            { k: 'program', t: 'Program' }, { k: 'regulation', t: 'Reg' },
                            { k: 'cgpa', t: 'CGPA', num: 1, f: (v) => n(v, 2) },
                            { k: 'last_sgpa', t: 'Last SGPA', num: 1, f: (v) => n(v, 2) },
                            { k: 'backlogs', t: 'Backlogs', num: 1, f: (v) => (v ? badge(v, 'risk') : badge('0', 'good')) },
                            { k: 'avg_pct', t: 'Avg %', num: 1, f: (v) => pc(v) },
                            { k: 'division', t: 'Division', f: (v) => esc(v || '—') },
                        ];
                        const renderStu = (rows) => table('tStu', stuCols, rows, { sortKey: 'cgpa' });
                        let curV = 'all';
                        const filterStu = (v) => {
                            curV = v;
                            const q = ($('#stuSearch').value || '').toLowerCase().trim();
                            let r = v === 'top' ? K.students.slice(0, 10) : v === 'bottom' ? K.students.slice(-10).reverse() : v === 'risk' ? K.students.filter((s) => s.at_risk) : K.students;
                            if (q) r = r.filter((s) => s.regno.toLowerCase().includes(q));
                            renderStu(r);
                        };
                        filterStu('all');
                        $('#segStudents').querySelectorAll('button').forEach((b) => b.addEventListener('click', () => {
                            $('#segStudents').querySelectorAll('button').forEach((x) => x.classList.remove('active'));
                            b.classList.add('active');
                            filterStu(b.dataset.v);
                        }));
                        $('#stuSearch').addEventListener('input', () => filterStu(curV));
                    }

                    // Raw Records Table
                    const recCols = [
                        { k: 'regno', t: 'Register No' }, { k: 'sem', t: 'Sem' }, { k: 'exam', t: 'Exam' },
                        {
                            k: 'type',
                            t: 'Type',
                            f: (v) => {
                                const val = String(v || '').trim().toUpperCase();
                                return val === 'REGULAR'
                                    ? `<span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;font-weight:700">REGULAR</span>`
                                    : `<span class="badge" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;font-weight:700">${esc(val || 'REAPPEAR')}</span>`;
                            }
                        },
                        { k: 'subcode', t: 'Subject' },
                        { k: 'fa', t: 'FA', num: 1, f: (v, r) => `${v}/${r.fa_c}` },
                        { k: 'sa', t: 'SA', num: 1, f: (v, r) => `${v}/${r.sa_c}` },
                        { k: 'pct', t: 'Marks %', num: 1, f: (v) => pc(v) },
                        { k: 'grade', t: 'Grade', f: (v) => badge(v, /F|AB/.test(v) ? 'risk' : 'good') },
                        { k: 'gp', t: 'GP', num: 1 },
                        { k: 'counted', t: 'Counted', f: (v) => (v ? 'Yes' : 'Replaced') },
                    ];
                    let recR = K.records;
                    const renderRec = () => {
                        const q = ($('#recSearch').value || '').toLowerCase().trim();
                        const ty = $('#recType').value;
                        let r = recR;
                        if (ty === 'counted') r = r.filter((x) => x.counted);
                        else if (ty === 'reappear') r = r.filter((x) => x.is_reappear);
                        else if (ty === 'fail') r = r.filter((x) => !x.passed);
                        if (q) r = r.filter((x) => (x.regno + ' ' + x.subcode + ' ' + x.exam + ' ' + x.grade).toLowerCase().includes(q));
                        $('#recCount').textContent = `${r.length} of ${recR.length} rows`;
                        table('tRec', recCols, r.slice(0, 300), { sortKey: 'regno', dir: 1 });
                    };
                    renderRec();
                    $('#recSearch').addEventListener('input', renderRec);
                    $('#recType').addEventListener('change', renderRec);

                    // ================= REAPPEAR REPORT TABLE =================
                    const repCols = [
                        { k: 'regno', t: 'Register No', f: (v) => `<a href="${location.pathname}?regno=${encodeURIComponent(v)}" class="fw-bold text-decoration-none">${esc(v)}</a>` },
                        { k: 'student_name', t: 'Student Name', f: (v) => `<span class="fw-semibold">${esc(v || 'N/A')}</span>` },
                        { k: 'exam_session', t: 'Exam Session', f: (v) => `<span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1">${esc(v)}</span>` },
                        { k: 'subcode', t: 'Subject Code', f: (v) => `<span class="badge font-monospace" style="background:#f8fafc;color:#0f172a;border:1px solid #cbd5e1;font-weight:700">${esc(v)}</span>` },
                        {
                            k: 'type',
                            t: 'Type',
                            f: (v) => {
                                const val = String(v || '').trim().toUpperCase();
                                return val === 'REGULAR'
                                    ? `<span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;font-weight:700">REGULAR</span>`
                                    : `<span class="badge" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;font-weight:700">${esc(val || 'REAPPEAR')}</span>`;
                            }
                        },
                        { k: 'grade_points', t: 'Grade Points', num: 1, f: (v, r) => `<span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-weight:700">${v ?? 0} (${esc(r.grade || 'F')})</span>` },
                    ];

                    let repR = K.reappear_report || [];
                    const sessSelect = $('#reapSessionFilter');
                    if (sessSelect && repR.length) {
                        const sessions = [...new Set(repR.map(x => x.exam_session).filter(Boolean))].sort();
                        sessions.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s;
                            opt.textContent = s;
                            sessSelect.appendChild(opt);
                        });
                    }

                    renderReap = () => {
                        const q = ($('#reapSearch') ? $('#reapSearch').value : '').toLowerCase().trim();
                        const ty = $('#reapTypeFilter') ? $('#reapTypeFilter').value : '';
                        const se = $('#reapSessionFilter') ? $('#reapSessionFilter').value : '';

                        let r = repR;
                        if (ty) r = r.filter(x => x.type === ty);
                        if (se) r = r.filter(x => x.exam_session === se);
                        if (q) r = r.filter(x => (x.regno + ' ' + x.student_name + ' ' + x.subcode + ' ' + x.exam_session).toLowerCase().includes(q));

                        if ($('#reapCountBadge')) {
                            $('#reapCountBadge').textContent = `Showing ${r.length} of ${repR.length} active backlogs`;
                        }

                        table('tReappearReport', repCols, r, { sortKey: 'regno', dir: 1 });
                    };

                    renderReap();
                    if ($('#reapSearch')) $('#reapSearch').addEventListener('input', renderReap);
                    if ($('#reapTypeFilter')) $('#reapTypeFilter').addEventListener('change', renderReap);
                    if ($('#reapSessionFilter')) $('#reapSessionFilter').addEventListener('change', renderReap);
                }

                function table(id, cols, rows, opts = {}) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    let sk = opts.sortKey || cols[0].k;
                    let dir = opts.dir ?? -1;
                    const draw = () => {
                        const sRows = [...rows].sort((a, b) => {
                            let va = a[sk], vb = b[sk];
                            if (va === null || va === undefined) return 1;
                            if (vb === null || vb === undefined) return -1;
                            return (va > vb ? 1 : va < vb ? -1 : 0) * dir;
                        });
                        const ths = cols.map((c) => `<th class="${c.num ? 'num' : ''} ${c.k === sk ? (dir > 0 ? 'asc' : 'desc') : ''}" data-k="${c.k}">${esc(c.t)}</th>`).join('');
                        const trs = sRows.map((r) => `<tr>${cols.map((c) => `<td class="${c.num ? 'num' : ''}">${c.f ? c.f(r[c.k], r) : esc(r[c.k])}</td>`).join('')}</tr>`).join('');
                        el.innerHTML = `<thead><tr>${ths}</tr></thead><tbody>${trs || '<tr><td colspan="' + cols.length + '" class="text-center text-muted py-3">No matching records</td></tr>'}</tbody>`;
                        el.querySelectorAll('th').forEach((th) => th.addEventListener('click', () => {
                            const k = th.dataset.k;
                            if (sk === k) dir = -dir; else { sk = k; dir = -1; }
                            draw();
                        }));
                    };
                    draw();
                }

                function badge(txt, kind) {
                    return `<span class="badge ${kind || 'muted'}">${esc(txt)}</span>`;
                }

                // ================= AI PANEL =================
                function aiPanel() {
                    const Q = K.quality || [];
                    const hi = Q.filter((q) => q.severity === 'high');
                    const I = K.insights || [];
                    $('#insights').innerHTML = I.length ? I.map((i) => `
                        <div class="insight">
                            <span class="rail" style="background:${i.kind === 'good' ? C.good : i.kind === 'risk' ? C.risk : C.watch}"></span>
                            <div>
                                <div class="t"><span class="badge ${i.kind}">${esc(i.area)}</span> ${esc(i.title)}</div>
                                <div class="x">${esc(i.text)}</div>
                            </div>
                        </div>`).join('') : '<p class="text-muted">No specific findings for this cohort.</p>';

                    $('#checks').innerHTML = Q.length ? Q.map((q) => `
                        <details class="check">
                            <summary><span class="badge ${q.severity === 'high' ? 'risk' : q.severity === 'medium' ? 'watch' : 'info'}">${esc(q.severity)}</span> <span class="t">${esc(q.title)} (${q.count})</span></summary>
                            <div class="body">${esc(q.detail)}${q.samples.map(s => `<code>${esc(s)}</code>`).join('')}</div>
                        </details>`).join('') : '<p class="text-success"><i class="bi bi-check-circle-fill me-1"></i> All data integrity checks passed cleanly.</p>';

                    const sugg = isStudent
                        ? ['What is my CGPA?', 'Which subject is the weakest?', 'Which subject is the strongest?', 'Any backlogs?', 'Is data correct?']
                        : ['What is the average CGPA?', 'Who are the top students?', 'Which students are at risk?', 'Which subject is the weakest?', 'Is data correct?'];
                    $('#sugg').innerHTML = sugg.map((s) => `<button type="button">${esc(s)}</button>`).join('');
                    $('#sugg').querySelectorAll('button').forEach((b) => b.addEventListener('click', () => { $('#askInput').value = b.textContent; answer(b.textContent); }));
                    $('#askBtn').addEventListener('click', () => answer($('#askInput').value));
                    $('#askInput').addEventListener('keydown', (e) => { if (e.key === 'Enter') answer(e.target.value); });
                }

                function answer(qRaw) {
                    const q = (qRaw || '').toLowerCase().trim();
                    const out = $('#answer');
                    const say = (html) => { out.innerHTML = html; };
                    if (!q) return say('Type a question first.');
                    if (/risk|backlog/.test(q)) {
                        return say(S.backlogs ? `<b>${S.backlogs}</b> pending backlogs across <b>${S.students_with_backlog}</b> student(s).` : 'No students at risk.');
                    }
                    if (/top/.test(q) && !isStudent) return say('Top students by CGPA: ' + K.students.slice(0, 5).map((s, i) => `${i + 1}. <b>${esc(s.regno)}</b> (CGPA ${n(s.cgpa, 2)})`).join(', ') + '.');
                    if (/weak|lowest/.test(q)) { const w = K.subjects.slice(-3).reverse(); return say('Weakest subjects: ' + w.map((s) => `<b>${esc(s.subcode)}</b> (pass ${pc(s.pass_pct)})`).join(', ') + '.'); }
                    if (/cgpa|gpa/.test(q)) return say(`Average CGPA: <b>${n(S.cgpa, 2)}</b> (median ${n(S.cgpa_median, 2)}, range ${n(S.cgpa_min, 2)}–${n(S.cgpa_max, 2)}).`);
                    say(`Cohort has ${S.students} students with average CGPA ${n(S.cgpa, 2)} and pass rate ${pc(S.pass_pct)}.`);
                }

                // ================= VIEW SWITCHING CONTROLLER =================
                let currentView = 'overview';
                function switchView(which) {
                    currentView = which;
                    $('#panel-overview').hidden = which !== 'overview';
                    $('#panel-detailed').hidden = which !== 'detailed';
                    if ($('#panel-report')) $('#panel-report').hidden = which !== 'report';
                    $('#panel-ai').hidden = which !== 'ai';

                    $('#btn-overview').classList.toggle('is-active', which === 'overview');
                    $('#btn-detailed').classList.toggle('is-active', which === 'detailed');
                    if ($('#btn-report')) $('#btn-report').classList.toggle('is-active', which === 'report');
                    $('#btn-ai').classList.toggle('is-active', which === 'ai');

                    if (which === 'overview') {
                        setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
                    } else if (which === 'detailed') {
                        initDetailedCharts();
                        setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
                    } else if (which === 'report') {
                        renderReap();
                        setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
                    } else if (which === 'ai') {
                        aiPanel();
                    }
                }

                $('#btn-overview').addEventListener('click', () => switchView('overview'));
                $('#btn-detailed').addEventListener('click', () => switchView('detailed'));
                if ($('#btn-report')) $('#btn-report').addEventListener('click', () => switchView('report'));
                $('#btn-ai').addEventListener('click', () => switchView('ai'));
                $('#btnGoDetailed').addEventListener('click', () => switchView('detailed'));

                // Card Click Actions (SaaS Micro-Interactions)
                $('#cardCgpa').addEventListener('click', () => {
                    switchView('overview');
                    setTimeout(() => $('#anchorGrades').scrollIntoView({ behavior: 'smooth' }), 50);
                });
                $('#cardPass').addEventListener('click', () => {
                    switchView('detailed');
                    activateSec('secSemesters');
                });
                $('#cardRisk').addEventListener('click', () => {
                    switchView('detailed');
                    activateSec('secStudents');
                    const rBtn = document.querySelector('#segStudents button[data-v="risk"]');
                    if (rBtn) rBtn.click();
                });
                $('#cardTotal').addEventListener('click', () => {
                    switchView('detailed');
                    activateSec('secStudents');
                    const aBtn = document.querySelector('#segStudents button[data-v="all"]');
                    if (aBtn) aBtn.click();
                });

                // Detailed Sub-Navigation
                function activateSec(secId) {
                    document.querySelectorAll('.detailed-section').forEach(s => s.classList.add('d-none'));
                    document.querySelectorAll('.detailed-nav-btn').forEach(b => b.classList.toggle('is-active', b.dataset.sec === secId));
                    const sec = document.getElementById(secId);
                    if (sec) sec.classList.remove('d-none');
                    setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
                }
                document.querySelectorAll('.detailed-nav-btn').forEach(btn => {
                    btn.addEventListener('click', () => activateSec(btn.dataset.sec));
                });

                // CSV Export
                function csv(name, rows, cols) {
                    const q = (v) => {
                        const s = String(v ?? "");
                        return (s.includes('"') || s.includes(",") || s.includes("\n")) ? '"' + s.replace(/"/g, '""') + '"' : s;
                    };
                    const text = [cols.join(","), ...rows.map((r) => cols.map((c) => q(r[c])).join(","))].join("\n");
                    const a = document.createElement("a");
                    a.href = URL.createObjectURL(new Blob(['﻿' + text], { type: 'text/csv;charset=utf-8' }));
                    a.download = name; document.body.appendChild(a); a.click(); a.remove();
                }
                const stamp = (isStudent ? M.student.regno : 'cohort') + '_' + new Date().toISOString().slice(0, 10);
                $('#btn-csv').addEventListener('click', () => csv(`marks_records_${stamp}.csv`, K.records, ['regno', 'program', 'sem', 'exam', 'type', 'mmno', 'subcode', 'fa', 'fa_c', 'sa', 'sa_c', 'total', 'total_c', 'pct', 'grade', 'gp', 'credits', 'sgpa', 'division', 'passed', 'counted']));
                $('#btn-subcsv').addEventListener('click', () => csv(`marks_subjects_${stamp}.csv`, K.subjects, ['subcode', 'semesters', 'results', 'attempts', 'reappears', 'credits', 'avg_pct', 'min_pct', 'max_pct', 'std_pct', 'fa_pct', 'sa_pct', 'gap', 'avg_gp', 'pass_pct']));
                const getReapRows = () => {
                    const q = ($('#reapSearch') ? $('#reapSearch').value : '').toLowerCase().trim();
                    const ty = $('#reapTypeFilter') ? $('#reapTypeFilter').value : '';
                    const se = $('#reapSessionFilter') ? $('#reapSessionFilter').value : '';
                    let r = K.reappear_report || [];
                    if (ty) r = r.filter(x => x.type === ty);
                    if (se) r = r.filter(x => x.exam_session === se);
                    if (q) r = r.filter(x => (x.regno + ' ' + x.student_name + ' ' + x.subcode + ' ' + x.exam_session).toLowerCase().includes(q));
                    return r;
                };

                const exportReapCsv = () => {
                    const rows = getReapRows();
                    if (!rows.length) {
                        alert('No active backlogs to export.');
                        return;
                    }
                    csv(`reappear_report_${stamp}.csv`, rows, ['regno', 'student_name', 'exam_session', 'subcode', 'type', 'grade_points']);
                };

                const printReapFormatted = (rowsToPrint) => {
                    const rows = rowsToPrint || getReapRows();
                    if (!rows.length) {
                        alert('No active backlogs to print.');
                        return;
                    }
                    const win = window.open('', '_blank');
                    if (!win) {
                        window.print();
                        return;
                    }
                    const now = new Date();
                    const dateStr = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    let html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>REAPPEAR REPORT — VFSTR CDOE</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 15mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; font-size: 11px; color: #0f172a; margin: 0; padding: 15px; }
        .hdr { border-bottom: 2px solid #c1121f; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-start; }
        .hdr h1 { margin: 0 0 2px 0; font-size: 15px; color: #c1121f; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .hdr p { margin: 0; font-size: 10px; color: #475569; }
        .meta { font-size: 10px; text-align: right; color: #64748b; line-height: 1.4; }
        .rep-title { font-size: 12.5px; font-weight: 700; color: #1e293b; margin-bottom: 8px; display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 5px; }
        th { background: #c1121f; color: #ffffff; padding: 6px 8px; text-align: left; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9.5px; font-weight: 700; }
        .badge-reg { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-reap { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-fail { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-weight: 700; }
        .mono { font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .ftr { margin-top: 15px; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        @media print {
            body { padding: 0; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tr:nth-child(even) td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="hdr">
        <div>
            <h1>Vignan's Foundation for Science, Technology and Research (VFSTR)</h1>
            <p>Directorate of Centre for Distance and Online Education (CDOE) — Examination Cell</p>
        </div>
        <div class="meta">
            <div><strong>Generated:</strong> ${dateStr}</div>
            <div><strong>Total Active Backlogs:</strong> ${rows.length}</div>
        </div>
    </div>
    <div class="rep-title">
        <span>ACTIVE REAPPEAR & PENDING BACKLOGS REPORT</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 35px; text-align: center;">#</th>
                <th style="width: 95px;">Register No</th>
                <th>Student Name</th>
                <th style="width: 120px; text-align: center;">Exam Session</th>
                <th style="width: 100px; text-align: center;">Subject Code</th>
                <th style="width: 85px; text-align: center;">Type</th>
                <th style="width: 90px; text-align: center;">Grade Points</th>
            </tr>
        </thead>
        <tbody>`;

                    rows.forEach((r, idx) => {
                        const isReg = String(r.type || '').toUpperCase() === 'REGULAR';
                        const typeBadge = isReg
                            ? '<span class="badge badge-reg">REGULAR</span>'
                            : '<span class="badge badge-reap">' + (r.type || 'REAPPEAR') + '</span>';
                        html += `
            <tr>
                <td style="text-align: center;" class="mono">${idx + 1}</td>
                <td class="mono"><strong>${esc(r.regno)}</strong></td>
                <td><strong>${esc(r.student_name || 'N/A')}</strong></td>
                <td style="text-align: center;">${esc(r.exam_session || '—')}</td>
                <td style="text-align: center;" class="mono"><strong>${esc(r.subcode)}</strong></td>
                <td style="text-align: center;">${typeBadge}</td>
                <td style="text-align: center;"><span class="badge badge-fail">${r.grade_points ?? 0} (${esc(r.grade || 'F')})</span></td>
            </tr>`;
                    });

                    html += `
        </tbody>
    </table>
    <div class="ftr">
        <span>VFSTR CDOE Examination Management System — Official Academic Report</span>
        <span>Total Records: ${rows.length}</span>
    </div>
    <script>
        window.addEventListener('load', () => { setTimeout(() => { window.print(); }, 250); });
    <\/script>
</body>
</html>`;

                    win.document.open();
                    win.document.write(html);
                    win.document.close();
                };

                const exportReapPdf = () => {
                    const rows = getReapRows();
                    if (!rows.length) {
                        alert('No active backlogs to export.');
                        return;
                    }

                    if (window.jspdf && window.jspdf.jsPDF) {
                        try {
                            const { jsPDF } = window.jspdf;
                            const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
                            const W = doc.internal.pageSize.getWidth();
                            const H = doc.internal.pageSize.getHeight();

                            // 1. Header Banner
                            doc.setFillColor(193, 18, 31); // #c1121f (Vignan red)
                            doc.rect(0, 0, W, 56, 'F');

                            doc.setFont('helvetica', 'bold');
                            doc.setFontSize(13);
                            doc.setTextColor(255, 255, 255);
                            doc.text("VIGNAN'S FOUNDATION FOR SCIENCE, TECHNOLOGY AND RESEARCH (VFSTR)", 40, 24);

                            doc.setFont('helvetica', 'normal');
                            doc.setFontSize(9);
                            doc.setTextColor(254, 226, 226);
                            doc.text('Directorate of Centre for Distance and Online Education (CDOE) — Examination Cell', 40, 42);

                            // 2. Report Subheading
                            doc.setFont('helvetica', 'bold');
                            doc.setFontSize(13);
                            doc.setTextColor(15, 23, 42);
                            doc.text('ACTIVE REAPPEAR & PENDING BACKLOGS REPORT', 40, 80);

                            doc.setFont('helvetica', 'normal');
                            doc.setFontSize(8.5);
                            doc.setTextColor(100, 116, 139);

                            const now = new Date();
                            const dateStr = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            const ty = $('#reapTypeFilter') ? $('#reapTypeFilter').value : '';
                            const se = $('#reapSessionFilter') ? $('#reapSessionFilter').value : '';
                            const filterStr = [ty ? `Type: ${ty}` : null, se ? `Session: ${se}` : null].filter(Boolean).join(' | ');

                            doc.text(`Generated: ${dateStr}   •   Total Active Backlogs: ${rows.length}${filterStr ? '   •   ' + filterStr : ''}`, 40, 95);

                            // 3. Table Data
                            const head = [['#', 'Register No', 'Student Name', 'Exam Session', 'Subject Code', 'Type', 'Grade Points', 'Grade']];
                            const body = rows.map((r, i) => [
                                i + 1,
                                r.regno,
                                r.student_name || 'N/A',
                                r.exam_session || '—',
                                r.subcode,
                                r.type || 'REAPPEAR',
                                r.grade_points ?? 0,
                                r.grade || 'F',
                            ]);

                            doc.autoTable({
                                head: head,
                                body: body,
                                startY: 108,
                                theme: 'grid',
                                headStyles: {
                                    fillColor: [193, 18, 31],
                                    textColor: [255, 255, 255],
                                    fontStyle: 'bold',
                                    fontSize: 8.5,
                                    cellPadding: 5,
                                    halign: 'left',
                                },
                                alternateRowStyles: {
                                    fillColor: [248, 250, 252],
                                },
                                styles: {
                                    fontSize: 8,
                                    textColor: [15, 23, 42],
                                    cellPadding: 4.5,
                                    lineColor: [226, 232, 240],
                                    lineWidth: 0.5,
                                    font: 'helvetica',
                                },
                                columnStyles: {
                                    0: { cellWidth: 30, halign: 'center' },
                                    1: { cellWidth: 85, fontStyle: 'bold' },
                                    2: { cellWidth: 195 },
                                    3: { cellWidth: 110, halign: 'center' },
                                    4: { cellWidth: 90, halign: 'center', fontStyle: 'bold' },
                                    5: { cellWidth: 80, halign: 'center' },
                                    6: { cellWidth: 65, halign: 'center' },
                                    7: { cellWidth: 45, halign: 'center', fontStyle: 'bold', textColor: [185, 28, 28] },
                                },
                                didParseCell: function (data) {
                                    if (data.section === 'body' && data.column.index === 5) {
                                        if (data.cell.raw === 'REGULAR') {
                                            data.cell.styles.textColor = [3, 105, 161];
                                            data.cell.styles.fontStyle = 'bold';
                                        } else {
                                            data.cell.styles.textColor = [185, 28, 28];
                                            data.cell.styles.fontStyle = 'bold';
                                        }
                                    }
                                },
                                didDrawPage: function (data) {
                                    const pageNum = doc.internal.getNumberOfPages();
                                    doc.setFontSize(8);
                                    doc.setTextColor(148, 163, 184);
                                    doc.text(`Page ${pageNum}`, W - 60, H - 15);
                                    doc.text('VFSTR CDOE Examination Management System — Strictly Confidential', 40, H - 15);
                                },
                                margin: { left: 40, right: 40, bottom: 28 },
                            });

                            doc.save(`reappear_report_${stamp}.pdf`);
                            return;
                        } catch (err) {
                            console.error('jsPDF generation failed, using print preview fallback:', err);
                        }
                    }
                    printReapFormatted(rows);
                };

                if ($('#btn-reappear-csv')) $('#btn-reappear-csv').addEventListener('click', exportReapCsv);
                if ($('#btnExportReapCsv')) $('#btnExportReapCsv').addEventListener('click', exportReapCsv);
                if ($('#btn-reappear-pdf')) $('#btn-reappear-pdf').addEventListener('click', exportReapPdf);
                if ($('#btnExportReapPdf')) $('#btnExportReapPdf').addEventListener('click', exportReapPdf);
                if ($('#btnPrintReap')) $('#btnPrintReap').addEventListener('click', () => printReapFormatted());

                // Initialize
                initOverviewCharts();
                initTables();
                switchView('overview');
            })();
        </script>
    @endverbatim
@endif
</div>
@endsection
