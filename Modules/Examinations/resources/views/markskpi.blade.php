<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marks KPI | Examinations</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=Newsreader:opsz,wght@6..72,500;6..72,600&display=swap" rel="stylesheet">
    @verbatim
        <style>
            :root{
                --ink:#1B2559; --ink-2:#4A5578; --ink-3:#7C86A2;
                --paper:#F4F6FA; --surface:#FFFFFF; --line:#DDE2EC; --line-2:#EDF0F6;
                --accent:#3346A8; --accent-soft:#E8EBFA;
                --good:#0E7C66; --good-soft:#E3F3EF;
                --watch:#B7791F; --watch-soft:#FBF1DF;
                --risk:#B42318; --risk-soft:#FBE7E5;
                --sans:"IBM Plex Sans", "Segoe UI", Roboto, Arial, sans-serif;
                --serif:"Newsreader", Georgia, "Times New Roman", serif;
            }
            *{box-sizing:border-box}
            html,body{margin:0;background:var(--paper);color:var(--ink);font-family:var(--sans);font-size:15px;line-height:1.5}
            body{font-variant-numeric:tabular-nums}
            a{color:var(--accent)}
            :focus-visible{outline:2px solid var(--accent);outline-offset:2px}
            .wrap{max-width:1440px;margin:0 auto;padding:20px 24px 48px}

            /* header + filters */
            .top{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:14px}
            .top h1{font-family:var(--serif);font-weight:600;font-size:30px;line-height:1.1;margin:0}
            .top p{margin:4px 0 0;color:var(--ink-2)}
            .filters{background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:12px 14px;display:grid;
                grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;align-items:end;margin-bottom:16px}
            .filters label{display:block;font-size:12.5px;color:var(--ink-2);margin-bottom:3px}
            .filters input,.filters select{width:100%;padding:7px 9px;border:1px solid var(--line);border-radius:6px;font:inherit;font-size:14px;color:var(--ink);background:#fff}
            .filters .actions{display:flex;gap:8px}
            .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 14px;border-radius:6px;border:1px solid var(--line);
                background:#fff;color:var(--ink);font:inherit;font-size:14px;font-weight:500;cursor:pointer;text-decoration:none;white-space:nowrap}
            .btn:hover{border-color:var(--ink-3)}
            .btn.primary{background:var(--ink);border-color:var(--ink);color:#fff}
            .btn.primary:hover{background:#26326f}

            /* view toggle */
            .bar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;margin-bottom:16px}
            .toggle{display:inline-flex;background:var(--surface);border:1px solid var(--line);border-radius:8px;padding:3px}
            .toggle button{border:0;background:transparent;padding:8px 18px;border-radius:6px;font:inherit;font-weight:600;color:var(--ink-2);cursor:pointer}
            .toggle button[aria-pressed="true"]{background:var(--ink);color:#fff}
            .bar .right{display:flex;gap:8px;flex-wrap:wrap}
            .notice{padding:10px 14px;border-radius:8px;margin-bottom:14px;border:1px solid}
            .notice.warn{background:var(--watch-soft);border-color:#EBD3A6;color:#6B4710}
            .notice.err{background:var(--risk-soft);border-color:#F0B9B3;color:#7A1C14}

            /* hero: transcript strip */
            .hero{display:grid;grid-template-columns:300px 1fr;background:var(--surface);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:16px}
            .hero .score{padding:20px 22px;border-right:1px solid var(--line);display:flex;flex-direction:column;justify-content:space-between}
            .hero .who{font-size:14px;color:var(--ink-2)}
            .hero .who strong{display:block;font-family:var(--serif);font-size:22px;color:var(--ink);font-weight:600}
            .gauge{position:relative;height:150px;margin-top:8px}
            .gauge .num{position:absolute;left:0;right:0;bottom:6px;text-align:center}
            .gauge .num b{font-family:var(--serif);font-size:46px;font-weight:600;line-height:1}
            .gauge .num span{display:block;font-size:13px;color:var(--ink-2)}
            .strip{display:flex;overflow-x:auto}
            .sem{flex:1 0 170px;padding:18px 18px 16px;border-right:1px solid var(--line-2);display:flex;flex-direction:column;gap:8px}
            .sem:last-child{border-right:0}
            .sem .lbl{font-size:13px;color:var(--ink-2);display:flex;justify-content:space-between;gap:6px}
            .sem .val{font-family:var(--serif);font-size:34px;font-weight:600;line-height:1}
            .sem .delta{font-size:13px;font-weight:600}
            .sem .stored{font-size:12.5px;color:var(--ink-3)}
            .sem .stored.off{color:var(--risk)}
            .chips{display:flex;flex-wrap:wrap;gap:4px;margin-top:auto}
            .chip{min-width:28px;padding:2px 6px;border-radius:4px;font-size:12px;font-weight:600;text-align:center;color:#fff}
            .meter{height:6px;background:var(--line-2);border-radius:3px;overflow:hidden}
            .meter i{display:block;height:100%;background:var(--good)}

            /* KPI tiles */
            .tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:22px}
            .tile{background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:13px 15px;border-left:4px solid var(--line)}
            .tile.good{border-left-color:var(--good)} .tile.watch{border-left-color:var(--watch)} .tile.risk{border-left-color:var(--risk)} .tile.info{border-left-color:var(--accent)}
            .tile .k{font-size:13px;color:var(--ink-2)}
            .tile .v{font-size:26px;font-weight:600;margin:2px 0 4px;line-height:1.15}
            .tile .s{font-size:12.5px;color:var(--ink-3)}
            .tile .meter{margin:6px 0 4px}
            .up{color:var(--good)} .down{color:var(--risk)} .flat{color:var(--ink-3)}

            /* sections + cards */
            .section{margin:26px 0 10px;display:flex;align-items:baseline;justify-content:space-between;gap:12px;flex-wrap:wrap}
            .section h2{font-family:var(--serif);font-size:22px;font-weight:600;margin:0}
            .section p{margin:0;color:var(--ink-2);font-size:14px}
            .grid{display:grid;gap:14px}
            .g2{grid-template-columns:repeat(2,minmax(0,1fr))}
            .g3{grid-template-columns:repeat(3,minmax(0,1fr))}
            .g21{grid-template-columns:2fr 1fr}
            .card{background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:14px 16px;min-width:0}
            .card h3{font-size:15px;font-weight:600;margin:0 0 2px}
            .card .sub{font-size:13px;color:var(--ink-3);margin:0 0 10px}
            .chart{position:relative;height:280px}
            .chart.tall{height:360px}
            .chart.short{height:220px}
            .seg{display:inline-flex;border:1px solid var(--line);border-radius:6px;overflow:hidden}
            .seg button{border:0;background:#fff;padding:5px 11px;font:inherit;font-size:13px;color:var(--ink-2);cursor:pointer;border-right:1px solid var(--line)}
            .seg button:last-child{border-right:0}
            .seg button[aria-pressed="true"]{background:var(--accent-soft);color:var(--accent);font-weight:600}
            .card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;margin-bottom:8px}

            /* tables */
            .tbl-wrap{overflow-x:auto;max-height:460px;overflow-y:auto;border:1px solid var(--line-2);border-radius:8px}
            table{width:100%;border-collapse:collapse;font-size:13.5px}
            th,td{padding:7px 10px;text-align:left;border-bottom:1px solid var(--line-2);white-space:nowrap}
            th{position:sticky;top:0;background:#F8F9FC;font-weight:600;color:var(--ink-2);cursor:pointer;user-select:none}
            th[data-sort]::after{content:"";margin-left:4px}
            th.asc::after{content:"\25B2";font-size:9px} th.desc::after{content:"\25BC";font-size:9px}
            td.num,th.num{text-align:right}
            tr:hover td{background:#FAFBFE}
            .badge{display:inline-block;padding:1px 8px;border-radius:10px;font-size:12px;font-weight:600}
            .badge.good{background:var(--good-soft);color:var(--good)} .badge.watch{background:var(--watch-soft);color:var(--watch)}
            .badge.risk{background:var(--risk-soft);color:var(--risk)} .badge.info{background:var(--accent-soft);color:var(--accent)}
            .badge.muted{background:var(--line-2);color:var(--ink-2)}
            .tbl-tools{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:8px}
            .tbl-tools input,.tbl-tools select{padding:6px 9px;border:1px solid var(--line);border-radius:6px;font:inherit;font-size:13.5px}
            .muted{color:var(--ink-3);font-size:13px}

            /* AI analytics */
            .ai-head{background:var(--ink);color:#fff;border-radius:12px;padding:18px 22px;margin-bottom:16px;display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:center}
            .ai-head h2{font-family:var(--serif);font-size:24px;margin:0;font-weight:600}
            .ai-head p{margin:4px 0 0;color:#C9D0E6;font-size:14px;max-width:70ch}
            .ai-head .badge{background:#ffffff1f;color:#fff}
            .insight{display:grid;grid-template-columns:4px 1fr;gap:12px;padding:12px 0;border-bottom:1px solid var(--line-2)}
            .insight:last-child{border-bottom:0}
            .insight .rail{border-radius:2px}
            .insight .t{font-weight:600;display:flex;gap:8px;align-items:center;flex-wrap:wrap}
            .insight .x{color:var(--ink-2);margin-top:2px;max-width:85ch}
            .actions-list{margin:0;padding-left:20px}
            .actions-list li{margin:6px 0;max-width:80ch}
            .check{border:1px solid var(--line);border-radius:8px;margin-bottom:8px}
            .check summary{padding:10px 12px;cursor:pointer;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
            .check summary .t{font-weight:600}
            .check .body{padding:0 12px 12px;color:var(--ink-2);font-size:13.5px}
            .check code{display:block;font-size:12.5px;background:#F8F9FC;border-radius:4px;padding:3px 8px;margin-top:4px;white-space:pre-wrap;color:var(--ink)}
            .ask{display:flex;gap:8px;margin:6px 0 8px}
            .ask input{flex:1;padding:9px 12px;border:1px solid var(--line);border-radius:6px;font:inherit}
            .sugg{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px}
            .sugg button{border:1px solid var(--line);background:#fff;border-radius:14px;padding:3px 10px;font:inherit;font-size:12.5px;color:var(--ink-2);cursor:pointer}
            .answer{background:var(--accent-soft);border-radius:8px;padding:10px 12px;min-height:44px}
            .empty{background:var(--surface);border:1px dashed var(--line);border-radius:12px;padding:40px 24px;text-align:center}
            .empty h2{font-family:var(--serif);margin:0 0 6px}
            .empty p{color:var(--ink-2);margin:0 0 14px}
            [hidden]{display:none !important}

            @media (max-width:1100px){.g3{grid-template-columns:repeat(2,minmax(0,1fr))}.g21{grid-template-columns:1fr}}
            @media (max-width:760px){.g2,.g3{grid-template-columns:1fr}.hero{grid-template-columns:1fr}.hero .score{border-right:0;border-bottom:1px solid var(--line)}.wrap{padding:14px}}
            @media (prefers-reduced-motion:reduce){*{animation:none !important;transition:none !important}}
            @media print{
                .filters,.bar,.ask,.sugg{display:none !important}
                #panel-kpi,#panel-ai{display:block !important}
                .card,.tile,.hero{break-inside:avoid}
                body{background:#fff}
                .tbl-wrap{max-height:none}
            }
        </style>
    @endverbatim
</head>
<body>
<div class="wrap">

    <header class="top">
        <div>
            <h1>Student marks KPI</h1>
            <p id="scopeLine">Results, grades, SGPA/CGPA and data checks from examinations.marks_cgpa</p>
        </div>
    </header>

    {{-- ===================== Filters ===================== --}}
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
            <button class="btn primary" type="submit">Show results</button>
            <a class="btn" href="{{ url()->current() }}">Clear</a>
        </div>
    </form>

    @if ($error)
        <div class="notice err">{{ $error }}</div>
    @endif

    @if (!$kpi || !empty($kpi['empty']))
        <div class="empty">
            @if ($kpi && !empty($kpi['empty']))
                <h2>No results match these filters</h2>
                <p>Check the register number or widen the program, semester or exam filters.</p>
            @else
                <h2>Choose what to analyse</h2>
                <p>Enter a register number for one student's report, or pick a program, regulation or exam for a cohort view.</p>
                <a class="btn" href="{{ url()->current() }}?all=1">Analyse all results</a>
            @endif
        </div>
    @else

        @if (!empty($kpi['meta']['truncated']))
            <div class="notice warn">Only the first {{ number_format($kpi['meta']['row_limit']) }} result rows were analysed. Narrow the filters for complete figures.</div>
        @endif

        {{-- ===================== The two view buttons ===================== --}}
        <div class="bar">
            <div class="toggle" role="group" aria-label="View">
                <button type="button" id="btn-kpi" aria-pressed="true">KPI dashboard</button>
                <button type="button" id="btn-ai" aria-pressed="false">AI analytics</button>
            </div>
            <div class="right">
                <button type="button" class="btn" id="btn-csv">Export records (CSV)</button>
                <button type="button" class="btn" id="btn-subcsv">Export subjects (CSV)</button>
                <button type="button" class="btn" onclick="window.print()">Print</button>
            </div>
        </div>

        {{-- ===================== KPI PANEL ===================== --}}
        <main id="panel-kpi">

            <section class="hero" aria-label="Overall score and semesters">
                <div class="score">
                    <div class="who" id="heroWho"></div>
                    <div class="gauge">
                        <canvas id="cGauge" aria-label="CGPA gauge"></canvas>
                        <div class="num"><b id="heroCgpa">-</b><span id="heroCgpaSub">CGPA out of 10</span></div>
                    </div>
                </div>
                <div class="strip" id="semStrip"></div>
            </section>

            <div class="tiles" id="tiles"></div>

            <div class="section"><h2>Grades and marks</h2><p>Counted attempt per subject (<span id="policyLbl"></span> attempt)</p></div>
            <div class="grid g3">
                <div class="card"><h3>Grade distribution</h3><p class="sub">Number of subject results per letter grade</p><div class="chart"><canvas id="cGrades"></canvas></div></div>
                <div class="card"><h3>Grade share</h3><p class="sub">Share of results by grade</p><div class="chart"><canvas id="cGradeShare"></canvas></div></div>
                <div class="card"><h3>Marks distribution</h3><p class="sub">Results by percentage band</p><div class="chart"><canvas id="cHist"></canvas></div></div>
            </div>

            <div class="section"><h2>Semesters and exams</h2><p>How performance moves over time</p></div>
            <div class="grid g21">
                <div class="card"><h3>SGPA and CGPA by semester</h3><p class="sub">Recomputed SGPA, stored SGPA (sgpa_perc) and running CGPA, with average marks</p><div class="chart tall"><canvas id="cSem"></canvas></div></div>
                <div class="card"><h3>Division</h3><p class="sub">Latest division recorded per semester</p><div class="chart tall"><canvas id="cDivision"></canvas></div></div>
            </div>
            <div class="grid g2" style="margin-top:14px">
                <div class="card"><h3>Exam sessions</h3><p class="sub">Regular and reappear results per exam month, with average marks and pass rate</p><div class="chart"><canvas id="cSessions"></canvas></div></div>
                <div class="card"><h3>Credits by semester</h3><p class="sub">Earned versus pending credits <span id="creditNote"></span></p><div class="chart"><canvas id="cCredits"></canvas></div></div>
            </div>
            <div class="card" style="margin-top:14px">
                <div class="card-head"><div><h3>Semester summary</h3><p class="sub">Click a column to sort</p></div></div>
                <div class="tbl-wrap"><table id="tSem"></table></div>
            </div>

            <div class="section"><h2>Subjects</h2><p>Strengths, weaknesses and spread per subject code</p></div>
            <div class="grid g2">
                <div class="card"><h3>Average marks by subject</h3><p class="sub">Sorted high to low, coloured by pass rate</p><div class="chart tall"><canvas id="cSubjects"></canvas></div></div>
                <div class="card"><h3>Range of marks per subject</h3><p class="sub">Lowest to highest, with the average marked</p><div class="chart tall"><canvas id="cRange"></canvas></div></div>
            </div>
            <div class="grid g2" style="margin-top:14px">
                <div class="card"><h3>Subject profile</h3><p class="sub">Average, internal and external % per subject</p><div class="chart tall"><canvas id="cRadar"></canvas></div></div>
                <div class="card"><h3>Grade points by subject</h3><p class="sub">Average grade points against credits</p><div class="chart tall"><canvas id="cGp"></canvas></div></div>
            </div>
            <div class="card" style="margin-top:14px">
                <div class="card-head"><div><h3>Subject analysis</h3><p class="sub">Gap = internal % minus external %. Gaps of <span id="gapLbl"></span>+ points are highlighted.</p></div></div>
                <div class="tbl-wrap"><table id="tSub"></table></div>
            </div>

            <div class="section"><h2>Internal and external assessment</h2><p>Formative (FA) against summative (SA) marks</p></div>
            <div class="grid g2">
                <div class="card"><h3>FA % versus SA % by subject</h3><p class="sub">Each component as a percentage of its maximum</p><div class="chart"><canvas id="cFaSa"></canvas></div></div>
                <div class="card"><h3>Does internal predict external?</h3><p class="sub" id="scatterSub">Each dot is one subject result</p><div class="chart"><canvas id="cScatter"></canvas></div></div>
            </div>

            <div class="section"><h2>Reappear and improvement</h2><p id="reapLine"></p></div>
            <div class="grid g21">
                <div class="card"><h3>Before and after reappear</h3><p class="sub">Marks % in the earlier attempt and the reappear attempt</p><div class="chart"><canvas id="cReappear"></canvas></div></div>
                <div class="card"><h3>Reappear outcome</h3><p class="sub">What each reappear attempt achieved</p><div class="chart"><canvas id="cReapOutcome"></canvas></div></div>
            </div>
            <div class="card" style="margin-top:14px">
                <div class="tbl-wrap"><table id="tReap"></table></div>
            </div>

            <div id="cohortOnly">
                <div class="section"><h2>Groups</h2><p>Compare programs, gender and regulation</p></div>
                <div class="grid g2">
                    <div class="card">
                        <div class="card-head">
                            <div><h3>Group comparison</h3><p class="sub">Average CGPA and pass rate</p></div>
                            <div class="seg" id="segGroup">
                                <button type="button" data-g="program" aria-pressed="true">Program</button>
                                <button type="button" data-g="gender" aria-pressed="false">Gender</button>
                                <button type="button" data-g="regulation" aria-pressed="false">Regulation</button>
                            </div>
                        </div>
                        <div class="chart"><canvas id="cGroups"></canvas></div>
                    </div>
                    <div class="card"><h3>CGPA distribution</h3><p class="sub">Students per CGPA band</p><div class="chart"><canvas id="cCgpaDist"></canvas></div></div>
                </div>

                <div class="section"><h2>Students</h2><p>Click a register number to open that student's report</p></div>
                <div class="card">
                    <div class="tbl-tools">
                        <div class="seg" id="segStudents">
                            <button type="button" data-v="top" aria-pressed="true">Top 10</button>
                            <button type="button" data-v="bottom" aria-pressed="false">Bottom 10</button>
                            <button type="button" data-v="risk" aria-pressed="false">At risk</button>
                            <button type="button" data-v="all" aria-pressed="false">All</button>
                        </div>
                        <input type="search" id="stuSearch" placeholder="Search register number" aria-label="Search students">
                    </div>
                    <div class="tbl-wrap"><table id="tStu"></table></div>
                </div>
            </div>

            <div class="section"><h2>Result records</h2><p>Every row loaded; "Counted" marks the attempt used for SGPA/CGPA</p></div>
            <div class="card">
                <div class="tbl-tools">
                    <input type="search" id="recSearch" placeholder="Search register no, subject, exam or grade" aria-label="Search records" style="min-width:280px">
                    <select id="recType" aria-label="Attempt type"><option value="">All attempts</option><option value="counted">Counted only</option><option value="reappear">Reappear only</option><option value="fail">Not cleared</option></select>
                    <span class="muted" id="recCount"></span>
                </div>
                <div class="tbl-wrap"><table id="tRec"></table></div>
            </div>
        </main>

        {{-- ===================== AI ANALYTICS PANEL ===================== --}}
        <section id="panel-ai" hidden>
            <div class="ai-head">
                <div>
                    <h2>AI analytics</h2>
                    <p>Insights, projections and data checks generated from these results with statistical rules on your server. No external AI service is used and no data leaves the system.</p>
                </div>
                <span class="badge">Rule-based statistics</span>
            </div>

            <div class="grid g21">
                <div class="card">
                    <h3>Key insights</h3>
                    <p class="sub">Green is a strength, amber needs watching, red needs action</p>
                    <div id="insights"></div>
                </div>
                <div>
                    <div class="card">
                        <h3>Recommended actions</h3>
                        <p class="sub">Derived from the insights and checks</p>
                        <ol class="actions-list" id="actions"></ol>
                    </div>
                    <div class="card" style="margin-top:14px">
                        <h3>SGPA projection</h3>
                        <p class="sub" id="fcSub"></p>
                        <div class="chart short"><canvas id="cForecast"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:14px">
                <h3>Ask about these results</h3>
                <p class="sub">Type a question in plain words. Answers come only from the data loaded on this page.</p>
                <div class="ask">
                    <input id="askInput" placeholder="e.g. Which subject is the weakest?" aria-label="Question">
                    <button class="btn primary" type="button" id="askBtn">Ask</button>
                </div>
                <div class="sugg" id="sugg"></div>
                <div class="answer" id="answer" aria-live="polite">Pick a suggested question or type your own.</div>
            </div>

            <div class="section"><h2>Data checks</h2><p>Problems found in the stored results. Fix high-severity items before publishing.</p></div>
            <div id="checks"></div>

            <div class="section"><h2>Risk indicators</h2><p>Signals that point to students or subjects needing support</p></div>
            <div class="grid g3" id="riskCards"></div>
        </section>

    @endif
</div>

@if ($kpi && empty($kpi['empty']))
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>window.MARKS_KPI = @json($kpi);</script>
    @verbatim
        <script>
            (function () {
                'use strict';
                const K = window.MARKS_KPI;
                if (!K || K.empty) return;

                // ---------- helpers ----------
                const $ = (s) => document.querySelector(s);
                const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                const isNum = (v) => v !== null && v !== undefined && v !== '' && !isNaN(v);
                const n = (v, d = 1) => (isNum(v) ? Number(v).toFixed(d) : '–');
                const pc = (v, d = 1) => (isNum(v) ? Number(v).toFixed(d) + '%' : '–');
                const root = getComputedStyle(document.documentElement);
                const V = (name) => root.getPropertyValue(name).trim();
                const C = {
                    ink: V('--ink'), ink2: V('--ink-2'), ink3: V('--ink-3'), line: V('--line'), line2: V('--line-2'),
                    accent: V('--accent'), good: V('--good'), watch: V('--watch'), risk: V('--risk'),
                };
                const PALETTE = [C.accent, C.good, C.watch, '#6C4AB6', '#2B8CBE', C.risk, '#5B7065', '#A35C8E'];
                const GRADE_COL = { O: '#0B5D4B', S: '#0E7C66', 'A+': '#2A9D8F', A: '#3FA796', 'B+': '#3E6FB0', B: '#4C78A8', C: '#B7791F', D: '#C2410C', E: '#9A3412', P: '#6B7280', F: '#B42318', AB: '#7A1C14' };
                const gradeCol = (g) => GRADE_COL[g] || '#8A93AD';
                const passCol = (p) => (p >= 90 ? C.good : p >= 75 ? C.watch : C.risk);
                const alpha = (hex, a) => { const h = hex.replace('#', ''); const i = parseInt(h, 16); return `rgba(${(i >> 16) & 255},${(i >> 8) & 255},${i & 255},${a})`; };

                const S = K.summary, M = K.meta, isStudent = M.mode === 'student';

                Chart.defaults.font.family = '"IBM Plex Sans","Segoe UI",Roboto,Arial,sans-serif';
                Chart.defaults.font.size = 12;
                Chart.defaults.color = C.ink2;
                Chart.defaults.borderColor = C.line2;
                Chart.defaults.plugins.legend.labels.boxWidth = 12;
                Chart.defaults.plugins.tooltip.backgroundColor = C.ink;
                Chart.defaults.maintainAspectRatio = false;

                const charts = {};
                const mk = (id, cfg) => { const el = document.getElementById(id); if (!el) return; if (charts[id]) charts[id].destroy(); charts[id] = new Chart(el, cfg); };
                const pctAxis = (title) => ({ min: 0, max: 100, title: { display: !!title, text: title }, ticks: { callback: (v) => v + '%' } });

                // ---------- header ----------
                $('#policyLbl').textContent = M.attempt_policy;
                $('#gapLbl').textContent = M.gap_alert;
                if (isStudent) {
                    const p = M.student;
                    $('#scopeLine').textContent = `Student report for ${p.regno}, ${p.program}, regulation ${p.regulation}`;
                    $('#cohortOnly').hidden = true;
                } else {
                    $('#scopeLine').textContent = `Cohort view: ${S.students} students, ${S.records} result rows, ${S.sessions} exam sessions`;
                }

                // ---------- hero ----------
                (function hero() {
                    if (isStudent) {
                        const p = M.student;
                        $('#heroWho').innerHTML = `<strong>${esc(p.regno)}</strong>${esc(p.program)}, ${esc(p.gender)}, regulation ${esc(p.regulation)}<br>${esc(p.division || 'Division not recorded')}`;
                    } else {
                        $('#heroWho').innerHTML = `<strong>${S.students} students</strong>Average CGPA, median ${n(S.cgpa_median, 2)}<br>Range ${n(S.cgpa_min, 2)} to ${n(S.cgpa_max, 2)}`;
                    }
                    $('#heroCgpa').textContent = n(S.cgpa, 2);
                    $('#heroCgpaSub').textContent = isStudent ? 'CGPA out of 10 (recomputed)' : 'Average CGPA out of 10';
                    const cg = Math.max(0, Math.min(10, S.cgpa || 0));
                    mk('cGauge', {
                        type: 'doughnut',
                        data: { datasets: [{ data: [cg, 10 - cg], backgroundColor: [cg >= 7.5 ? C.good : cg >= 6 ? C.watch : C.risk, C.line2], borderWidth: 0 }] },
                        options: { rotation: -90, circumference: 180, cutout: '78%', plugins: { legend: { display: false }, tooltip: { enabled: false } } },
                    });

                    const strip = $('#semStrip');
                    const stuSems = isStudent && K.students.length ? K.students[0].sems : null;
                    strip.innerHTML = K.semesters.map((s, i) => {
                        const prev = i ? K.semesters[i - 1].sgpa : null;
                        const d = isNum(prev) && isNum(s.sgpa) ? s.sgpa - prev : null;
                        const dHtml = d === null ? '<span class="delta flat">First semester</span>'
                            : `<span class="delta ${d > 0.05 ? 'up' : d < -0.05 ? 'down' : 'flat'}">${d > 0.05 ? '▲' : d < -0.05 ? '▼' : '■'} ${d >= 0 ? '+' : ''}${n(d, 2)}</span>`;
                        const off = isNum(s.sgpa_reported) && Math.abs(s.sgpa_reported - s.sgpa) > 0.05;
                        const stored = isNum(s.sgpa_reported) ? `<span class="stored ${off ? 'off' : ''}">Stored SGPA ${n(s.sgpa_reported, 2)}${off ? ' (differs)' : ''}</span>` : '<span class="stored">No stored SGPA</span>';
                        let foot;
                        if (isStudent) {
                            const subs = K.records.filter((r) => r.counted && r.sem === s.label);
                            foot = `<div class="chips">${subs.map((r) => `<span class="chip" title="${esc(r.subcode)}: ${n(r.pct)}%" style="background:${gradeCol(r.grade)}">${esc(r.grade)}</span>`).join('')}</div>`;
                        } else {
                            foot = `<div class="muted">Pass ${pc(s.pass_pct)}</div><div class="meter"><i style="width:${s.pass_pct || 0}%;background:${passCol(s.pass_pct)}"></i></div>`;
                        }
                        return `<div class="sem"><div class="lbl"><span>${esc(s.label)}</span><span>${pc(s.avg_pct)}</span></div>
        <div class="val">${n(s.sgpa, 2)}</div>${dHtml}${stored}${foot}</div>`;
                    }).join('');
                })();

                // ---------- KPI tiles ----------
                (function tiles() {
                    const t = [];
                    const tile = (cls, k, v, s, meter) => t.push(`<div class="tile ${cls}"><div class="k">${k}</div><div class="v">${v}</div>${meter !== undefined ? `<div class="meter"><i style="width:${Math.max(0, Math.min(100, meter || 0))}%;background:${cls === 'risk' ? C.risk : cls === 'watch' ? C.watch : C.good}"></i></div>` : ''}<div class="s">${s}</div></div>`);
                    const lvl = (v, g, w) => (v >= g ? 'good' : v >= w ? 'watch' : 'risk');

                    tile(lvl(S.avg_pct, 75, 60), 'Average marks', pc(S.avg_pct), `Median ${pc(S.median_pct)}, spread ±${n(S.std_pct)}`);
                    tile(lvl(S.pass_pct, 95, 80), 'Pass rate', pc(S.pass_pct), `First-attempt pass ${pc(S.first_attempt_pass_pct)}`, S.pass_pct);
                    tile(lvl(S.credit_completion_pct, 100, 85), 'Credits earned', `${n(S.credits_earned, 0)} / ${n(S.credits_registered, 0)}`, `${pc(S.credit_completion_pct)} completed`, S.credit_completion_pct);
                    tile(S.backlogs ? 'risk' : 'good', 'Pending backlogs', S.backlogs, isStudent ? (S.backlogs ? 'Subjects still to clear' : 'All subjects cleared') : `${S.students_with_backlog} students affected`);
                    tile(S.fa_sa_gap >= M.gap_alert ? 'risk' : S.fa_sa_gap >= 10 ? 'watch' : 'good', 'Internal vs external', `${pc(S.fa_pct, 0)} / ${pc(S.sa_pct, 0)}`, `Gap ${n(S.fa_sa_gap)} points`);
                    tile('info', 'Top grades', pc(S.top_grade_pct), `Results with 9+ grade points; average GP ${n(S.avg_grade_points, 2)}`, S.top_grade_pct);
                    const sDiff = isNum(S.avg_sgpa_reported) && isNum(S.cgpa) ? S.avg_sgpa_reported - S.cgpa : null;
                    tile(sDiff !== null && Math.abs(sDiff) > 0.05 ? 'watch' : 'info', 'Stored SGPA (average)', n(S.avg_sgpa_reported, 2), 'From sgpa_perc; compare with recomputed CGPA ' + n(S.cgpa, 2));
                    tile(S.distinction_pct >= 50 ? 'good' : 'info', 'Distinction', isStudent ? (S.distinction_students ? 'Yes' : 'No') : pc(S.distinction_pct), isStudent ? 'Latest division' : `${S.distinction_students} students`);
                    tile(S.reappear_attempts ? 'watch' : 'good', 'Reappear attempts', S.reappear_attempts, isNum(S.reappear_avg_gain) ? `Average change ${n(S.reappear_avg_gain)} points` : 'No reappear attempts');
                    if (isStudent) {
                        tile('good', 'Strongest subject', esc(S.best_subject ? S.best_subject.code : '–'), S.best_subject ? `${pc(S.best_subject.pct)} average` : '');
                        tile('watch', 'Weakest subject', esc(S.weakest_subject ? S.weakest_subject.code : '–'), S.weakest_subject ? `${pc(S.weakest_subject.pct)} average` : '');
                    } else {
                        tile(S.at_risk_students ? 'risk' : 'good', 'Students at risk', S.at_risk_students, `Backlog, CGPA < ${M.risk_cgpa} or a falling SGPA`);
                        tile('info', 'Students with no backlog', pc(S.all_clear_pct), `${S.students - S.students_with_backlog} of ${S.students}`, S.all_clear_pct);
                    }
                    tile('info', 'Coverage', `${S.subjects} subjects`, `${S.semesters} semesters, ${S.sessions} exam sessions, ${S.records} rows`);
                    tile(S.quality_issues ? 'risk' : 'good', 'Data checks', S.quality_issues ? `${S.quality_issues} flags` : 'Clean', S.quality_issues ? '<a href="#" id="goChecks">Review in AI analytics</a>' : 'No problems found');
                    $('#tiles').innerHTML = t.join('');
                    const go = $('#goChecks');
                    if (go) go.addEventListener('click', (e) => { e.preventDefault(); showPanel('ai'); setTimeout(() => $('#checks').scrollIntoView({ behavior: 'smooth' }), 50); });
                })();

                // ---------- KPI charts ----------
                function kpiCharts() {
                    const G = K.grades;
                    mk('cGrades', { type: 'bar', data: { labels: G.map((g) => g.grade), datasets: [{ label: 'Results', data: G.map((g) => g.count), backgroundColor: G.map((g) => gradeCol(g.grade)), borderRadius: 4 }] },
                        options: { plugins: { legend: { display: false }, tooltip: { callbacks: { afterLabel: (c) => `Share ${G[c.dataIndex].share}%, avg GP ${G[c.dataIndex].avg_gp}` } } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
                    mk('cGradeShare', { type: 'doughnut', data: { labels: G.map((g) => g.grade), datasets: [{ data: G.map((g) => g.count), backgroundColor: G.map((g) => gradeCol(g.grade)), borderWidth: 2, borderColor: '#fff' }] },
                        options: { cutout: '58%', plugins: { legend: { position: 'right' } } } });
                    mk('cHist', { type: 'bar', data: { labels: K.histogram.map((h) => h.label + '%'), datasets: [{ label: 'Results', data: K.histogram.map((h) => h.count), backgroundColor: K.histogram.map((h, i) => (i === 0 ? C.risk : i < 3 ? C.watch : i < 5 ? C.accent : C.good)), borderRadius: 4 }] },
                        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });

                    const Sm = K.semesters;
                    mk('cSem', {
                        data: {
                            labels: Sm.map((s) => s.label),
                            datasets: [
                                { type: 'line', label: 'SGPA (recomputed)', data: Sm.map((s) => s.sgpa), borderColor: C.accent, backgroundColor: C.accent, tension: 0.25, yAxisID: 'g', pointRadius: 4 },
                                { type: 'line', label: 'SGPA (stored)', data: Sm.map((s) => s.sgpa_reported), borderColor: C.watch, backgroundColor: C.watch, borderDash: [5, 4], tension: 0.25, yAxisID: 'g', pointRadius: 4 },
                                { type: 'line', label: 'CGPA (running)', data: Sm.map((s) => s.cgpa_running), borderColor: C.good, backgroundColor: C.good, tension: 0.25, yAxisID: 'g', pointRadius: 4 },
                                { type: 'bar', label: 'Average marks %', data: Sm.map((s) => s.avg_pct), backgroundColor: alpha(C.ink3, 0.18), borderRadius: 4, yAxisID: 'p' },
                            ],
                        },
                        options: { interaction: { mode: 'index', intersect: false }, scales: { g: { position: 'left', min: 0, max: 10, title: { display: true, text: 'Grade points' } }, p: { position: 'right', ...pctAxis('Marks %'), grid: { display: false } } } },
                    });
                    const D = K.divisions;
                    mk('cDivision', { type: 'doughnut', data: { labels: D.map((d) => d.division), datasets: [{ data: D.map((d) => d.count), backgroundColor: D.map((d, i) => (/DISTINCTION/i.test(d.division) ? C.good : /FIRST/i.test(d.division) ? C.accent : /SECOND/i.test(d.division) ? C.watch : /FAIL|PROMOTED|THIRD|PASS/i.test(d.division) ? C.risk : PALETTE[i % PALETTE.length])), borderWidth: 2, borderColor: '#fff' }] },
                        options: { cutout: '55%', plugins: { legend: { position: 'bottom' } } } });

                    const Se = K.sessions;
                    mk('cSessions', {
                        data: {
                            labels: Se.map((s) => s.exam),
                            datasets: [
                                { type: 'bar', label: 'Regular', data: Se.map((s) => s.regular), backgroundColor: C.accent, stack: 'n', yAxisID: 'n', borderRadius: 3 },
                                { type: 'bar', label: 'Reappear', data: Se.map((s) => s.reappear), backgroundColor: C.watch, stack: 'n', yAxisID: 'n', borderRadius: 3 },
                                { type: 'line', label: 'Average marks %', data: Se.map((s) => s.avg_pct), borderColor: C.good, backgroundColor: C.good, yAxisID: 'p', tension: 0.25 },
                                { type: 'line', label: 'Pass %', data: Se.map((s) => s.pass_pct), borderColor: C.risk, backgroundColor: C.risk, borderDash: [4, 4], yAxisID: 'p', tension: 0.25 },
                            ],
                        },
                        options: { interaction: { mode: 'index', intersect: false }, scales: { n: { stacked: true, beginAtZero: true, title: { display: true, text: 'Results' }, ticks: { precision: 0 } }, p: { position: 'right', ...pctAxis(), grid: { display: false } }, x: { stacked: true } } },
                    });
                    $('#creditNote').textContent = isStudent ? '' : '(average per student)';
                    mk('cCredits', {
                        type: 'bar',
                        data: { labels: Sm.map((s) => s.label), datasets: [
                                { label: 'Earned', data: Sm.map((s) => s.credits_earned_avg), backgroundColor: C.good, borderRadius: 3 },
                                { label: 'Pending', data: Sm.map((s) => Math.max(0, (s.credits_reg_avg || 0) - (s.credits_earned_avg || 0))), backgroundColor: C.risk, borderRadius: 3 },
                            ] },
                        options: { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Credits' } } } },
                    });

                    const Sb = K.subjects;
                    mk('cSubjects', { type: 'bar', data: { labels: Sb.map((s) => s.subcode), datasets: [{ label: 'Average %', data: Sb.map((s) => s.avg_pct), backgroundColor: Sb.map((s) => passCol(s.pass_pct)), borderRadius: 3 }] },
                        options: { indexAxis: 'y', plugins: { legend: { display: false }, tooltip: { callbacks: { afterLabel: (c) => `Pass ${Sb[c.dataIndex].pass_pct}%, ${Sb[c.dataIndex].results} results` } } }, scales: { x: pctAxis() } } });
                    mk('cRange', {
                        data: { labels: Sb.map((s) => s.subcode), datasets: [
                                { type: 'bar', label: 'Lowest to highest', data: Sb.map((s) => [s.min_pct, s.max_pct]), backgroundColor: alpha(C.accent, 0.25), borderColor: C.accent, borderWidth: 1, borderSkipped: false, borderRadius: 3 },
                                { type: 'line', label: 'Average', data: Sb.map((s) => s.avg_pct), showLine: false, borderColor: C.ink, backgroundColor: C.ink, pointStyle: 'rectRot', pointRadius: 6 },
                            ] },
                        options: { scales: { y: pctAxis() }, plugins: { tooltip: { callbacks: { label: (c) => (Array.isArray(c.raw) ? `Range ${c.raw[0]}% to ${c.raw[1]}%` : `Average ${c.raw}%`) } } } },
                    });
                    mk('cRadar', {
                        type: 'radar',
                        data: { labels: Sb.map((s) => s.subcode), datasets: [
                                { label: 'Average %', data: Sb.map((s) => s.avg_pct), borderColor: C.accent, backgroundColor: alpha(C.accent, 0.15) },
                                { label: 'Internal (FA) %', data: Sb.map((s) => s.fa_pct), borderColor: C.good, backgroundColor: alpha(C.good, 0.08) },
                                { label: 'External (SA) %', data: Sb.map((s) => s.sa_pct), borderColor: C.watch, backgroundColor: alpha(C.watch, 0.08) },
                            ] },
                        options: { scales: { r: { min: 0, max: 100, ticks: { stepSize: 20, backdropColor: 'transparent' }, grid: { color: C.line }, angleLines: { color: C.line } } } },
                    });
                    mk('cGp', {
                        data: { labels: Sb.map((s) => s.subcode), datasets: [
                                { type: 'bar', label: 'Average grade points', data: Sb.map((s) => s.avg_gp), backgroundColor: Sb.map((s) => (s.avg_gp >= 8 ? C.good : s.avg_gp >= 6 ? C.accent : C.risk)), borderRadius: 3, yAxisID: 'g' },
                                { type: 'line', label: 'Credits', data: Sb.map((s) => s.credits), borderColor: C.ink3, backgroundColor: C.ink3, yAxisID: 'c', showLine: false, pointRadius: 5, pointStyle: 'triangle' },
                            ] },
                        options: { scales: { g: { min: 0, max: 10, title: { display: true, text: 'Grade points' } }, c: { position: 'right', beginAtZero: true, grid: { display: false }, title: { display: true, text: 'Credits' } } } },
                    });
                    mk('cFaSa', {
                        type: 'bar',
                        data: { labels: Sb.map((s) => s.subcode), datasets: [
                                { label: 'Internal (FA) %', data: Sb.map((s) => s.fa_pct), backgroundColor: C.good, borderRadius: 3 },
                                { label: 'External (SA) %', data: Sb.map((s) => s.sa_pct), backgroundColor: C.watch, borderRadius: 3 },
                            ] },
                        options: { scales: { y: pctAxis() }, plugins: { tooltip: { callbacks: { afterBody: (it) => `Gap ${Sb[it[0].dataIndex].gap} points` } } } },
                    });
                    const P = K.scatter.points;
                    $('#scatterSub').textContent = `Each dot is one subject result. Correlation r = ${n(K.scatter.r, 2)}; dots on the dashed line score the same internally and externally.`;
                    mk('cScatter', {
                        type: 'scatter',
                        data: { datasets: [
                                { label: 'Results', data: P, backgroundColor: P.map((p) => (p.x - p.y >= M.gap_alert ? C.risk : alpha(C.accent, 0.6))), pointRadius: 5 },
                                { label: 'Equal', type: 'line', data: [{ x: 0, y: 0 }, { x: 100, y: 100 }], borderColor: C.ink3, borderDash: [4, 4], pointRadius: 0, borderWidth: 1 },
                            ] },
                        options: { plugins: { legend: { display: false }, tooltip: { filter: (i) => i.datasetIndex === 0, callbacks: { label: (c) => (c.raw.s ? `${c.raw.s}${isStudent ? '' : ' (' + c.raw.g + ')'}: FA ${c.raw.x}%, SA ${c.raw.y}%` : '') } } }, scales: { x: { ...pctAxis('Internal (FA) %') }, y: { ...pctAxis('External (SA) %') } } },
                    });

                    const R = K.reappear, RR = R.rows.slice(0, 30);
                    $('#reapLine').textContent = R.summary.attempts
                        ? `${R.summary.attempts} reappear attempts: ${R.summary.backlogs_cleared} cleared a backlog, ${R.summary.improvement_only} improvement, ${R.summary.still_pending} still pending`
                        : 'No reappear attempts in these results';
                    mk('cReappear', {
                        type: 'bar',
                        data: { labels: RR.map((r) => (isStudent ? r.subcode : `${r.regno} ${r.subcode}`)), datasets: [
                                { label: 'Earlier attempt %', data: RR.map((r) => r.before_pct), backgroundColor: alpha(C.ink3, 0.5), borderRadius: 3 },
                                { label: 'Reappear %', data: RR.map((r) => r.after_pct), backgroundColor: RR.map((r) => (r.gain > 0 ? C.good : r.gain < 0 ? C.risk : C.watch)), borderRadius: 3 },
                            ] },
                        options: { scales: { y: pctAxis() } },
                    });
                    const ro = R.summary;
                    mk('cReapOutcome', {
                        type: 'doughnut',
                        data: { labels: ['Backlog cleared', 'Improvement', 'Still pending', 'Identical to earlier'], datasets: [{ data: [ro.backlogs_cleared, ro.improvement_only, ro.still_pending, ro.identical], backgroundColor: [C.good, C.accent, C.risk, C.watch], borderWidth: 2, borderColor: '#fff' }] },
                        options: { cutout: '55%', plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { footer: () => 'Identical attempts are also counted in their own outcome' } } } },
                    });

                    if (!isStudent) { groupChart('program'); cgpaDist(); }
                }

                function groupChart(key) {
                    const Gp = K.groups[key] || [];
                    mk('cGroups', {
                        data: { labels: Gp.map((g) => `${g.name} (${g.students})`), datasets: [
                                { type: 'bar', label: 'Average CGPA', data: Gp.map((g) => g.avg_cgpa), backgroundColor: Gp.map((_, i) => PALETTE[i % PALETTE.length]), borderRadius: 4, yAxisID: 'g' },
                                { type: 'line', label: 'Pass %', data: Gp.map((g) => g.pass_pct), borderColor: C.risk, backgroundColor: C.risk, yAxisID: 'p', showLine: false, pointRadius: 6 },
                            ] },
                        options: { scales: { g: { min: 0, max: 10, title: { display: true, text: 'CGPA' } }, p: { position: 'right', ...pctAxis(), grid: { display: false } } } },
                    });
                }
                function cgpaDist() {
                    const bins = [[0, 5, '< 5'], [5, 6, '5–6'], [6, 7, '6–7'], [7, 8, '7–8'], [8, 9, '8–9'], [9, 10.01, '9–10']];
                    const counts = bins.map(([lo, hi]) => K.students.filter((s) => isNum(s.cgpa) && s.cgpa >= lo && s.cgpa < hi).length);
                    mk('cCgpaDist', { type: 'bar', data: { labels: bins.map((b) => b[2]), datasets: [{ label: 'Students', data: counts, backgroundColor: [C.risk, C.risk, C.watch, C.accent, C.good, C.good], borderRadius: 4 }] },
                        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
                }

                // ---------- sortable tables ----------
                function table(id, cols, rows, opts = {}) {
                    const el = document.getElementById(id);
                    let sortKey = opts.sortKey || null, dir = opts.dir || -1;
                    const draw = () => {
                        let data = rows.slice();
                        if (sortKey) {
                            data.sort((a, b) => {
                                const x = a[sortKey], y = b[sortKey];
                                if (isNum(x) && isNum(y)) return (x - y) * dir;
                                return String(x ?? '').localeCompare(String(y ?? '')) * dir;
                            });
                        }
                        const limit = opts.limit || 500;
                        el.innerHTML = `<thead><tr>${cols.map((c) => `<th data-sort="${c.k}" class="${c.num ? 'num' : ''} ${sortKey === c.k ? (dir > 0 ? 'asc' : 'desc') : ''}">${c.t}</th>`).join('')}</tr></thead>
        <tbody>${data.slice(0, limit).map((r) => `<tr>${cols.map((c) => `<td class="${c.num ? 'num' : ''}">${c.f ? c.f(r[c.k], r) : esc(r[c.k] ?? '–')}</td>`).join('')}</tr>`).join('')}
        ${data.length === 0 ? `<tr><td colspan="${cols.length}" class="muted">No rows</td></tr>` : ''}</tbody>`;
                        el.querySelectorAll('th').forEach((th) => th.addEventListener('click', () => {
                            const k = th.dataset.sort; dir = sortKey === k ? -dir : -1; sortKey = k; draw();
                        }));
                        if (opts.after) opts.after(data);
                    };
                    draw();
                    return { set: (r) => { rows = r; draw(); } };
                }
                const badge = (txt, cls) => `<span class="badge ${cls}">${esc(txt)}</span>`;

                function kpiTables() {
                    table('tSem', [
                        { k: 'label', t: 'Semester' }, { k: 'students', t: 'Students', num: 1 }, { k: 'subject_results', t: 'Results', num: 1 },
                        { k: 'sgpa', t: 'SGPA (recomputed)', num: 1, f: (v) => n(v, 2) },
                        { k: 'sgpa_reported', t: 'SGPA (stored)', num: 1, f: (v, r) => (isNum(v) && Math.abs(v - r.sgpa) > 0.05 ? badge(n(v, 2), 'risk') : n(v, 2)) },
                        { k: 'cgpa_running', t: 'CGPA so far', num: 1, f: (v) => n(v, 2) },
                        { k: 'avg_pct', t: 'Avg %', num: 1, f: (v) => pc(v) }, { k: 'fa_pct', t: 'FA %', num: 1, f: (v) => pc(v) }, { k: 'sa_pct', t: 'SA %', num: 1, f: (v) => pc(v) },
                        { k: 'pass_pct', t: 'Pass %', num: 1, f: (v) => badge(pc(v), v >= 95 ? 'good' : v >= 80 ? 'watch' : 'risk') },
                        { k: 'credits_earned_avg', t: 'Credits earned', num: 1, f: (v, r) => `${n(v, 0)} / ${n(r.credits_reg_avg, 0)}` },
                    ], K.semesters, { sortKey: 'label', dir: 1 });

                    table('tSub', [
                        { k: 'subcode', t: 'Subject' }, { k: 'semesters', t: 'Semester' }, { k: 'results', t: 'Results', num: 1 }, { k: 'attempts', t: 'Attempts', num: 1 },
                        { k: 'reappears', t: 'Reappears', num: 1 }, { k: 'credits', t: 'Credits', num: 1 },
                        { k: 'avg_pct', t: 'Avg %', num: 1, f: (v) => pc(v) }, { k: 'min_pct', t: 'Min %', num: 1, f: (v) => pc(v) }, { k: 'max_pct', t: 'Max %', num: 1, f: (v) => pc(v) },
                        { k: 'std_pct', t: 'Spread', num: 1, f: (v) => n(v) },
                        { k: 'fa_pct', t: 'FA %', num: 1, f: (v) => pc(v) }, { k: 'sa_pct', t: 'SA %', num: 1, f: (v) => pc(v) },
                        { k: 'gap', t: 'Gap', num: 1, f: (v) => (isNum(v) && v >= M.gap_alert ? badge(n(v), 'risk') : isNum(v) && v >= 10 ? badge(n(v), 'watch') : n(v)) },
                        { k: 'avg_gp', t: 'Avg GP', num: 1, f: (v) => n(v, 2) },
                        { k: 'pass_pct', t: 'Pass %', num: 1, f: (v) => badge(pc(v), v >= 95 ? 'good' : v >= 80 ? 'watch' : 'risk') },
                    ], K.subjects, { sortKey: 'avg_pct' });

                    table('tReap', [
                        { k: 'regno', t: 'Register no' }, { k: 'subcode', t: 'Subject' }, { k: 'semester', t: 'Semester' },
                        { k: 'before_exam', t: 'Earlier exam' }, { k: 'before_pct', t: 'Earlier %', num: 1, f: (v) => pc(v) }, { k: 'before_grade', t: 'Grade' },
                        { k: 'after_exam', t: 'Reappear exam' }, { k: 'after_pct', t: 'Reappear %', num: 1, f: (v) => pc(v) }, { k: 'after_grade', t: 'Grade' },
                        { k: 'gain', t: 'Change', num: 1, f: (v) => `<span class="${v > 0 ? 'up' : v < 0 ? 'down' : 'flat'}">${v > 0 ? '+' : ''}${n(v)}</span>` },
                        { k: 'kind', t: 'Outcome', f: (v) => badge(v, v === 'Backlog cleared' ? 'good' : v === 'Still pending' ? 'risk' : 'info') },
                        { k: 'identical', t: 'Check', f: (v) => (v ? badge('Identical to earlier', 'watch') : '') },
                    ], K.reappear.rows, { sortKey: 'regno', dir: 1 });

                    // records
                    const recCols = [
                        { k: 'regno', t: 'Register no' }, { k: 'sem', t: 'Sem' }, { k: 'exam', t: 'Exam' }, { k: 'type', t: 'Type', f: (v) => badge(v, v === 'REGULAR' ? 'muted' : 'watch') },
                        { k: 'subcode', t: 'Subject' }, { k: 'fa', t: 'FA', num: 1, f: (v, r) => `${n(v, 0)}/${n(r.fa_c, 0)}` }, { k: 'sa', t: 'SA', num: 1, f: (v, r) => `${n(v, 0)}/${n(r.sa_c, 0)}` },
                        { k: 'total', t: 'Total', num: 1, f: (v, r) => `${n(v, 0)}/${n(r.total_c, 0)}` }, { k: 'pct', t: '%', num: 1, f: (v) => n(v) },
                        { k: 'grade', t: 'Grade', f: (v) => `<span class="chip" style="background:${gradeCol(v)}">${esc(v)}</span>` }, { k: 'gp', t: 'GP', num: 1 },
                        { k: 'credits', t: 'Credits', num: 1 }, { k: 'sgpa', t: 'Stored SGPA', num: 1, f: (v) => n(v, 2) }, { k: 'division', t: 'Division' },
                        { k: 'counted', t: 'Status', f: (v, r) => (r.passed ? '' : badge('Not cleared', 'risk') + ' ') + (v ? badge('Counted', 'good') : badge('Superseded', 'muted')) },
                    ];
                    const recT = table('tRec', recCols, K.records, { limit: 400, after: (d) => { $('#recCount').textContent = `${d.length} rows${d.length > 400 ? ', showing first 400' : ''}${M.row_count > K.records.length ? ` (of ${M.row_count} loaded)` : ''}`; } });
                    const filterRec = () => {
                        const q = $('#recSearch').value.trim().toLowerCase(), t = $('#recType').value;
                        recT.set(K.records.filter((r) => (!q || [r.regno, r.subcode, r.exam, r.grade, r.sem, r.type].join(' ').toLowerCase().includes(q))
                            && (!t || (t === 'counted' && r.counted) || (t === 'reappear' && r.type !== 'REGULAR') || (t === 'fail' && !r.passed))));
                    };
                    $('#recSearch').addEventListener('input', filterRec);
                    $('#recType').addEventListener('change', filterRec);

                    if (!isStudent) {
                        const base = location.pathname;
                        const stuCols = [
                            { k: 'regno', t: 'Register no', f: (v) => `<a href="${base}?regno=${encodeURIComponent(v)}">${esc(v)}</a>` }, { k: 'program', t: 'Program' }, { k: 'gender', t: 'Gender' },
                            { k: 'cgpa', t: 'CGPA', num: 1, f: (v) => n(v, 2) }, { k: 'avg_pct', t: 'Avg %', num: 1, f: (v) => pc(v) },
                            { k: 'last_sgpa', t: 'Last SGPA', num: 1, f: (v) => n(v, 2) },
                            { k: 'delta', t: 'Change', num: 1, f: (v) => (isNum(v) ? `<span class="${v > 0.05 ? 'up' : v < -0.05 ? 'down' : 'flat'}">${v > 0 ? '+' : ''}${n(v, 2)}</span>` : '–') },
                            { k: 'backlogs', t: 'Backlogs', num: 1, f: (v) => (v ? badge(v, 'risk') : '0') },
                            { k: 'credits_earned', t: 'Credits', num: 1, f: (v, r) => `${n(v, 0)} / ${n(r.credits_reg, 0)}` },
                            { k: 'division', t: 'Division' }, { k: 'at_risk', t: 'Risk', f: (v) => (v ? badge('At risk', 'risk') : badge('On track', 'good')) },
                        ];
                        let view = 'top';
                        const pick = () => {
                            const q = $('#stuSearch').value.trim().toLowerCase();
                            let list = K.students.filter((s) => !q || s.regno.toLowerCase().includes(q));
                            if (view === 'top') list = list.slice(0, 10);
                            if (view === 'bottom') list = list.slice(-10).reverse();
                            if (view === 'risk') list = list.filter((s) => s.at_risk);
                            return list;
                        };
                        const stuT = table('tStu', stuCols, pick(), { sortKey: view === 'bottom' ? 'cgpa' : null });
                        document.querySelectorAll('#segStudents button').forEach((b) => b.addEventListener('click', () => {
                            document.querySelectorAll('#segStudents button').forEach((x) => x.setAttribute('aria-pressed', x === b));
                            view = b.dataset.v; stuT.set(pick());
                        }));
                        $('#stuSearch').addEventListener('input', () => stuT.set(pick()));
                        document.querySelectorAll('#segGroup button').forEach((b) => b.addEventListener('click', () => {
                            document.querySelectorAll('#segGroup button').forEach((x) => x.setAttribute('aria-pressed', x === b));
                            groupChart(b.dataset.g);
                        }));
                    }
                }

                // ---------- AI analytics ----------
                let aiDone = false;
                function aiPanel() {
                    if (aiDone) return; aiDone = true;
                    const kindCol = { good: C.good, watch: C.watch, risk: C.risk, info: C.accent };
                    const kindLbl = { good: 'Strength', watch: 'Watch', risk: 'Action needed', info: 'Observation' };
                    const order = { risk: 0, watch: 1, good: 2, info: 3 };
                    const ins = K.insights.slice().sort((a, b) => order[a.kind] - order[b.kind]);
                    $('#insights').innerHTML = ins.map((i) => `<div class="insight"><div class="rail" style="background:${kindCol[i.kind]}"></div>
      <div><div class="t">${esc(i.title)} ${badge(kindLbl[i.kind], i.kind)} <span class="muted">${esc(i.area)}</span></div><div class="x">${esc(i.text)}</div></div></div>`).join('');

                    // recommended actions
                    const A = [];
                    const Q = K.quality, hi = Q.filter((q) => q.severity === 'high');
                    if (hi.length) A.push(`Correct the flagged rows before publishing: ${hi.map((q) => `${q.title} (${q.count})`).join(', ')}.`);
                    if (Q.some((q) => q.id === 'cgpa_missing')) A.push(`Populate cgpa_perc from the recomputed CGPA (${n(S.cgpa, 2)}${isStudent ? '' : ' average'}) so reports and transcripts agree.`);
                    if (Q.some((q) => q.id === 'identical_reappear')) A.push('Confirm the reappear rows that copy the earlier attempt are genuine re-examinations, or remove them.');
                    if (S.backlogs) {
                        const pend = K.records.filter((r) => r.counted && !r.passed).map((r) => r.subcode);
                        A.push(isStudent ? `Register for the next supplementary exam in ${[...new Set(pend)].join(', ')}.` : `Plan supplementary-exam coaching for ${S.students_with_backlog} students with ${S.backlogs} pending subjects.`);
                    }
                    const gapSubs = K.subjects.filter((s) => isNum(s.gap) && s.gap >= M.gap_alert).sort((a, b) => b.gap - a.gap);
                    if (gapSubs.length) A.push(`Review internal assessment in ${gapSubs.slice(0, 3).map((s) => s.subcode).join(', ')}: internal marks run ${M.gap_alert}+ points above external.`);
                    if (S.weakest_subject) A.push(isStudent ? `Focus revision on ${S.weakest_subject.code} (${pc(S.weakest_subject.pct)}), especially external-exam practice.` : `Schedule remedial sessions for ${K.subjects.slice(-2).map((s) => s.subcode).join(' and ')}.`);
                    if (!isStudent && S.at_risk_students) A.push(`Assign mentors to the ${S.at_risk_students} at-risk students listed on the KPI dashboard.`);
                    if (K.forecast.available && K.forecast.slope < -0.1) A.push('SGPA is trending down; check semester workload and attendance early next term.');
                    if (!A.length) A.push('No corrective action needed. Keep monitoring each new exam session.');
                    $('#actions').innerHTML = A.map((a) => `<li>${esc(a)}</li>`).join('');

                    // forecast
                    const F = K.forecast;
                    const sgpas = isStudent && K.students.length ? K.students[0].sems.map((s) => s.sgpa) : K.semesters.map((s) => s.sgpa);
                    const labels = isStudent && K.students.length ? K.students[0].sems.map((s) => s.label) : K.semesters.map((s) => s.label);
                    if (F.available) {
                        $('#fcSub').textContent = `Straight-line fit over ${F.points} semesters. Next SGPA about ${n(F.next_sgpa, 2)} (confidence ${F.confidence}).`;
                        mk('cForecast', {
                            type: 'line',
                            data: { labels: [...labels, 'Next'], datasets: [
                                    { label: 'Actual SGPA', data: [...sgpas, null], borderColor: C.accent, backgroundColor: C.accent, tension: 0.2, pointRadius: 4 },
                                    { label: 'Projected', data: [...sgpas.map((v, i) => (i === sgpas.length - 1 ? v : null)), F.next_sgpa], borderColor: C.watch, backgroundColor: C.watch, borderDash: [5, 4], pointRadius: 4 },
                                ] },
                            options: { scales: { y: { min: 0, max: 10 } } },
                        });
                    } else {
                        $('#fcSub').textContent = F.reason;
                    }

                    // data checks
                    const sevCls = { high: 'risk', medium: 'watch', info: 'info' };
                    $('#checks').innerHTML = Q.length ? Q.map((q) => `<details class="check" ${q.severity === 'high' ? 'open' : ''}>
        <summary>${badge(q.severity === 'high' ? 'High' : q.severity === 'medium' ? 'Medium' : 'Info', sevCls[q.severity])}<span class="t">${esc(q.title)}</span><span class="muted">${q.count} row(s)</span></summary>
        <div class="body">${esc(q.detail)}${q.samples.map((s) => `<code>${esc(s)}</code>`).join('')}${q.count > q.samples.length ? `<div class="muted" style="margin-top:4px">and ${q.count - q.samples.length} more</div>` : ''}</div>
      </details>`).join('') : '<div class="card">All checks passed.</div>';

                    // risk indicators
                    const R = [];
                    const risk = (cls, k, v, s) => R.push(`<div class="tile ${cls}"><div class="k">${k}</div><div class="v">${v}</div><div class="s">${s}</div></div>`);
                    risk(S.backlogs ? 'risk' : 'good', 'Pending backlogs', S.backlogs, isStudent ? 'Subjects not yet cleared' : `${S.students_with_backlog} students`);
                    risk(S.fa_sa_gap >= M.gap_alert ? 'risk' : S.fa_sa_gap >= 10 ? 'watch' : 'good', 'Internal-external gap', `${n(S.fa_sa_gap)} pts`, `${gapSubs.length} subject(s) over ${M.gap_alert} points`);
                    const low = K.subjects.filter((s) => s.pass_pct < 75).length;
                    risk(low ? 'risk' : 'good', 'Low pass-rate subjects', low, 'Pass rate under 75%');
                    const fall = K.semesters.length > 1 ? K.semesters[K.semesters.length - 1].sgpa - K.semesters[K.semesters.length - 2].sgpa : null;
                    risk(isNum(fall) && fall <= -0.5 ? 'risk' : isNum(fall) && fall < 0 ? 'watch' : 'good', 'Latest SGPA change', isNum(fall) ? `${fall > 0 ? '+' : ''}${n(fall, 2)}` : '–', 'Last semester against the one before');
                    risk(S.std_pct > 15 ? 'watch' : 'good', 'Consistency', `±${n(S.std_pct)}`, 'Standard deviation of marks %');
                    risk(hi.length ? 'risk' : 'good', 'Data integrity', hi.length ? `${hi.reduce((a, q) => a + q.count, 0)} rows` : 'Clean', 'High-severity data checks');
                    if (!isStudent) {
                        risk(S.at_risk_students ? 'risk' : 'good', 'Students at risk', S.at_risk_students, `${n(S.at_risk_students / S.students * 100)}% of the cohort`);
                        const below = K.students.filter((s) => isNum(s.cgpa) && s.cgpa < M.risk_cgpa).length;
                        risk(below ? 'risk' : 'good', `CGPA below ${M.risk_cgpa}`, below, 'Students');
                        const drop = K.students.filter((s) => isNum(s.delta) && s.delta <= -1).length;
                        risk(drop ? 'watch' : 'good', 'Sharp SGPA drops', drop, 'Students down 1+ point');
                    }
                    $('#riskCards').innerHTML = R.join('');

                    // question box
                    const sugg = isStudent
                        ? ['What is my CGPA?', 'Which subject is the weakest?', 'Which subject is the strongest?', 'Show SGPA by semester', 'Any backlogs?', 'Compare internal and external', 'How did reappear go?', 'Is the data correct?']
                        : ['What is the average CGPA?', 'Who are the top students?', 'Which students are at risk?', 'Which subject is the weakest?', 'Compare programs', 'Compare gender', 'Pass rate by semester', 'Is the data correct?'];
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
                    const subMatch = K.subjects.find((s) => q.includes(s.subcode.toLowerCase()));
                    const semMatch = q.match(/(?:sem(?:ester)?|s)\s*(\d)/);
                    const stuMatch = !isStudent && K.students.find((s) => q.includes(s.regno.toLowerCase()));

                    if (stuMatch) return say(`<b>${esc(stuMatch.regno)}</b> (${esc(stuMatch.program)}): CGPA ${n(stuMatch.cgpa, 2)}, average ${pc(stuMatch.avg_pct)}, ${stuMatch.backlogs} backlog(s). <a href="${location.pathname}?regno=${encodeURIComponent(stuMatch.regno)}">Open full report</a>.`);
                    if (subMatch) { const s = subMatch; return say(`<b>${esc(s.subcode)}</b>: average ${pc(s.avg_pct)} (range ${pc(s.min_pct)}–${pc(s.max_pct)}), internal ${pc(s.fa_pct)}, external ${pc(s.sa_pct)}, pass ${pc(s.pass_pct)}, average GP ${n(s.avg_gp, 2)} over ${s.results} result(s).`); }
                    if (/weak|lowest|worst|poor|difficult|hard/.test(q)) { const w = K.subjects.slice(-3).reverse(); return say('Weakest subjects: ' + w.map((s) => `<b>${esc(s.subcode)}</b> ${pc(s.avg_pct)} (pass ${pc(s.pass_pct)})`).join(', ') + '.'); }
                    if (/strong|best subject|highest subject|top subject/.test(q)) { const w = K.subjects.slice(0, 3); return say('Strongest subjects: ' + w.map((s) => `<b>${esc(s.subcode)}</b> ${pc(s.avg_pct)}`).join(', ') + '.'); }
                    if (/risk|mentor|support|struggl/.test(q)) {
                        if (isStudent) return say(S.backlogs || S.cgpa < M.risk_cgpa ? `This student needs support: ${S.backlogs} backlog(s), CGPA ${n(S.cgpa, 2)}.` : `Not at risk: no backlogs and CGPA ${n(S.cgpa, 2)}.`);
                        const r = K.students.filter((s) => s.at_risk);
                        return say(r.length ? `<b>${r.length}</b> at-risk students: ` + r.slice(0, 12).map((s) => `${esc(s.regno)} (CGPA ${n(s.cgpa, 2)}, ${s.backlogs} backlog)`).join(', ') + (r.length > 12 ? '…' : '') : 'No students are at risk.');
                    }
                    if (/top|topper|rank|best student|highest cgpa/.test(q) && !isStudent) return say('Top students by CGPA: ' + K.students.slice(0, 5).map((s, i) => `${i + 1}. <b>${esc(s.regno)}</b> ${n(s.cgpa, 2)}`).join(', ') + '.');
                    if (/bottom|lowest cgpa|least/.test(q) && !isStudent) return say('Lowest CGPA: ' + K.students.slice(-5).reverse().map((s) => `<b>${esc(s.regno)}</b> ${n(s.cgpa, 2)}`).join(', ') + '.');
                    if (/program|course|branch/.test(q) && !isStudent) return say(K.groups.program.map((g) => `<b>${esc(g.name)}</b>: CGPA ${n(g.avg_cgpa, 2)}, pass ${pc(g.pass_pct)} (${g.students} students)`).join('<br>'));
                    if (/gender|male|female|girls|boys/.test(q) && !isStudent) return say(K.groups.gender.map((g) => `<b>${esc(g.name)}</b>: CGPA ${n(g.avg_cgpa, 2)}, pass ${pc(g.pass_pct)} (${g.students} students)`).join('<br>'));
                    if (/regulation/.test(q) && !isStudent) return say(K.groups.regulation.map((g) => `<b>${esc(g.name)}</b>: CGPA ${n(g.avg_cgpa, 2)}, pass ${pc(g.pass_pct)}`).join('<br>'));
                    if (/sgpa|semester|sem\b|trend|progress/.test(q) || semMatch) {
                        let rows = K.semesters;
                        if (semMatch) rows = rows.filter((s) => s.label.endsWith('S' + semMatch[1]) || s.label.includes('S' + semMatch[1]));
                        if (/pass/.test(q)) return say(rows.map((s) => `<b>${esc(s.label)}</b>: pass ${pc(s.pass_pct)}`).join('<br>'));
                        return say(rows.map((s) => `<b>${esc(s.label)}</b>: SGPA ${n(s.sgpa, 2)} (stored ${n(s.sgpa_reported, 2)}), average ${pc(s.avg_pct)}, CGPA so far ${n(s.cgpa_running, 2)}`).join('<br>') || 'No semester matches that.');
                    }
                    if (/cgpa|overall|gpa/.test(q)) return say(`${isStudent ? 'Recomputed CGPA' : 'Average CGPA'}: <b>${n(S.cgpa, 2)}</b>${isStudent ? '' : ` (median ${n(S.cgpa_median, 2)}, range ${n(S.cgpa_min, 2)}–${n(S.cgpa_max, 2)})`}. Stored SGPA average is ${n(S.avg_sgpa_reported, 2)}.`);
                    if (/backlog|fail|arrear|pending|clear/.test(q)) {
                        const pend = K.records.filter((r) => r.counted && !r.passed);
                        return say(S.backlogs ? `<b>${S.backlogs}</b> pending: ` + pend.slice(0, 15).map((r) => `${isStudent ? '' : esc(r.regno) + ' '}${esc(r.subcode)}`).join(', ') + (pend.length > 15 ? '…' : '') : 'No pending backlogs. Every counted subject is cleared.');
                    }
                    if (/internal|external|fa\b|sa\b|formative|summative/.test(q)) return say(`Internal (FA) ${pc(S.fa_pct)} against external (SA) ${pc(S.sa_pct)}; gap ${n(S.fa_sa_gap)} points. Correlation r = ${n(K.scatter.r, 2)}.`);
                    if (/reappear|supply|supplementary|improv|re-?exam/.test(q)) { const r = K.reappear.summary; return say(r.attempts ? `${r.attempts} reappear attempts: ${r.backlogs_cleared} cleared a backlog, ${r.improvement_only} improvement, ${r.still_pending} pending, average change ${n(r.avg_gain)} points, ${r.identical} identical to the earlier attempt.` : 'No reappear attempts.'); }
                    if (/grade|distribution/.test(q)) return say(K.grades.map((g) => `<b>${esc(g.grade)}</b> ${g.count} (${g.share}%)`).join(', '));
                    if (/pass/.test(q)) return say(`Pass rate ${pc(S.pass_pct)}; first-attempt pass ${pc(S.first_attempt_pass_pct)}.`);
                    if (/credit/.test(q)) return say(`Credits earned ${n(S.credits_earned, 0)} of ${n(S.credits_registered, 0)} (${pc(S.credit_completion_pct)}).`);
                    if (/data|correct|error|mismatch|check|quality|wrong/.test(q)) return say(K.quality.length ? K.quality.map((x) => `${badge(x.severity, x.severity === 'high' ? 'risk' : x.severity === 'medium' ? 'watch' : 'info')} ${esc(x.title)} (${x.count})`).join('<br>') : 'All data checks passed.');
                    if (/predict|forecast|next|future|project/.test(q)) return say(K.forecast.available ? `Projected next SGPA about <b>${n(K.forecast.next_sgpa, 2)}</b> (confidence ${K.forecast.confidence}).` : K.forecast.reason);
                    if (/division|distinction|class/.test(q)) return say(K.divisions.map((d) => `${esc(d.division)}: ${d.count}`).join('<br>'));
                    if (/average|mean|marks|percent/.test(q)) return say(`Average marks ${pc(S.avg_pct)}, median ${pc(S.median_pct)}, range ${pc(S.min_pct)}–${pc(S.max_pct)}.`);
                    say('I can answer questions about CGPA, SGPA by semester, subjects (or a subject code), backlogs, internal vs external, reappear results, grades, credits, divisions, projections and data checks' + (isStudent ? '.' : ', plus top or at-risk students, programs and gender.'));
                }

                // ---------- view toggle ----------
                function showPanel(which) {
                    const ai = which === 'ai';
                    $('#panel-kpi').hidden = ai; $('#panel-ai').hidden = !ai;
                    $('#btn-kpi').setAttribute('aria-pressed', !ai); $('#btn-ai').setAttribute('aria-pressed', ai);
                    if (ai) aiPanel();
                    history.replaceState(null, '', location.pathname + location.search + (ai ? '#ai' : ''));
                }
                $('#btn-kpi').addEventListener('click', () => showPanel('kpi'));
                $('#btn-ai').addEventListener('click', () => showPanel('ai'));
                window.addEventListener('beforeprint', () => aiPanel());

                // ---------- CSV export ----------
                function csv(name, rows, cols) {
                    const q = (v) => { const s = String(v ?? ''); return /[",\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s; };
                    const text = [cols.join(','), ...rows.map((r) => cols.map((c) => q(r[c])).join(','))].join('\n');
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(new Blob(['\ufeff' + text], { type: 'text/csv;charset=utf-8' }));
                    a.download = name; document.body.appendChild(a); a.click(); a.remove();
                }
                const stamp = (isStudent ? M.student.regno : 'cohort') + '_' + new Date().toISOString().slice(0, 10);
                $('#btn-csv').addEventListener('click', () => csv(`marks_records_${stamp}.csv`, K.records, ['regno', 'program', 'sem', 'exam', 'type', 'mmno', 'subcode', 'fa', 'fa_c', 'sa', 'sa_c', 'total', 'total_c', 'pct', 'grade', 'gp', 'credits', 'sgpa', 'division', 'passed', 'counted']));
                $('#btn-subcsv').addEventListener('click', () => csv(`marks_subjects_${stamp}.csv`, K.subjects, ['subcode', 'semesters', 'results', 'attempts', 'reappears', 'credits', 'avg_pct', 'min_pct', 'max_pct', 'std_pct', 'fa_pct', 'sa_pct', 'gap', 'avg_gp', 'pass_pct']));

                // ---------- go ----------
                kpiCharts();
                kpiTables();
                if (location.hash === '#ai') showPanel('ai');
            })();
        </script>
    @endverbatim
@endif
</body>
</html>
