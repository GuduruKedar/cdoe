{{-- A4 Mark Memo --}}
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vignan Online - Mark Memo</title>
    <style>
        *{box-sizing:border-box}html,body{margin:0;padding:0;background:#eef2f5;font-family:"Liberation Sans",Arial,sans-serif;color:#151515}
        .toolbar{position:sticky;top:0;z-index:5;background:#fff;border-bottom:1px solid #d8e2ea;padding:10px 16px;display:flex;justify-content:center;gap:10px}
        .toolbar button{border:1px solid #1f5f99;background:#1f5f99;color:#fff;border-radius:6px;padding:9px 16px;font-weight:700;cursor:pointer}
        .memo-wrap{display:flex;justify-content:center;background:#eef2f5;padding:18px;overflow:auto}
        .memo-a4{width:210mm;min-height:297mm;background:#fff;box-shadow:0 10px 35px rgba(0,0,0,.12);padding:4mm;border:4.5mm solid #d7e8fb;flex:0 0 auto}
        .memo-inner{min-height:288mm;border:1.05mm solid #6ba9df;padding:7.5mm 6.5mm;position:relative}
        .memo-head{text-align:center;height:31mm;display:flex;align-items:center;justify-content:center}
        .memo-head img{width:88mm;height:auto;max-height:26mm;object-fit:contain;display:block}
        .memo-title{margin:1.5mm -6.5mm 8mm;background:#dce8f8;border-top:.3mm solid #7ea2c5;border-bottom:.3mm solid #7ea2c5;height:8mm;display:flex;align-items:center;justify-content:center}
        .memo-title span{background:#4a7fd9;color:#fff;padding:1.05mm 4.2mm;font-weight:700;font-size:3.05mm;letter-spacing:.15mm}
        .memo-meta{display:grid;grid-template-columns:1fr 1fr;column-gap:18mm;row-gap:2.2mm;font-size:2.75mm;margin:0 3mm 9mm}
        .memo-meta .pair{display:grid;grid-template-columns:28mm 3.5mm 1fr;align-items:start}
        .memo-meta .k{font-weight:700;color:#171717}
        .memo-meta .v{font-weight:700;color:#173f73;text-transform:uppercase;line-height:1.25}
        .memo-table{width:100%;border-collapse:collapse;font-size:2.7mm;margin-top:1mm;table-layout:fixed}
        .memo-table th,.memo-table td{border:.24mm solid #bcc6d0;padding:2.55mm 1.5mm;text-align:center;vertical-align:middle}
        .memo-table th{font-weight:700;background:#f2f2f2;color:#171717}
        .memo-table th:nth-child(1){width:10%}.memo-table th:nth-child(2){width:15%}.memo-table th:nth-child(3){width:41%}.memo-table th:nth-child(n+4){width:6.8%}
        .memo-table td.title{text-align:left;line-height:1.28;text-transform:uppercase}
        .memo-summary{display:grid;grid-template-columns:1fr 1fr;column-gap:18mm;row-gap:2.5mm;font-size:2.7mm;margin:12mm 3mm 0}
        .memo-summary .pair{display:grid;grid-template-columns:29mm 3.5mm 1fr}
        .memo-summary .pair>div:first-child{font-weight:700}
        .memo-legend{display:grid;grid-template-columns:repeat(3,1fr);column-gap:12mm;row-gap:3mm;font-size:2.6mm;margin:11mm 3mm 0}
        .memo-legend .pair{display:grid;grid-template-columns:12mm 3.5mm 1fr}
        .memo-legend .pair>div:first-child{font-weight:700}
        .memo-sign{display:flex;justify-content:space-between;align-items:flex-end;margin:18mm 3mm 0;font-size:2.6mm}
        .memo-date strong{font-weight:700}
        .memo-signature{text-align:center;min-width:53mm;font-weight:700}
        .signature-script{font-family:"Segoe Script",cursive;color:#0b9a66;font-size:7mm;transform:rotate(-6deg);line-height:1;margin-bottom:1mm}
        @media(max-width:760px){.memo-wrap{padding:8px;display:block;text-align:center}.memo-a4{transform:none;margin:0 auto;zoom:.50}}


        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html,
            body {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .toolbar {
                display: none !important;
            }

            .memo-wrap {
                display: block !important;
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: #fff !important;
            }

            .memo-a4 {
                position: relative !important;
                width: 210mm !important;
                height: 297mm !important;
                min-height: 297mm !important;
                margin: 0 !important;
                padding: 4mm !important;
                border: 4.5mm solid #d7e8fb !important;
                box-shadow: none !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
                display: block !important;
            }

            .memo-inner {
                width: 100% !important;
                height: 100% !important;
                min-height: 0 !important;
                padding: 5mm 6.5mm !important;
                box-sizing: border-box !important;
                display: block !important;
                overflow: visible !important;
            }

            .memo-head {
                height: 25mm !important;
            }

            .memo-head img {
                max-height: 22mm !important;
                width: 80mm !important;
                object-fit: contain !important;
            }

            .memo-title {
                margin-top: 1.5mm !important;
                margin-bottom: 6mm !important;
            }

            .memo-meta {
                margin-bottom: 7mm !important;
                row-gap: 2mm !important;
            }

            .memo-table {
                margin-top: 1mm !important;
                font-size: 2.7mm !important;
            }

            .memo-table th,
            .memo-table td {
                padding: 2mm 1.2mm !important;
            }

            .memo-summary {
                margin: 10mm 3mm 0 !important;
            }

            .memo-legend {
                margin: 8mm 3mm 0 !important;
            }

            .memo-sign {
                margin: 15mm 3mm 0 !important;
            }
        }



    </style>
</head>
<body>

<div class="toolbar">
    <button onclick="window.print()">Print / Save PDF (A4)</button>
</div>

<div class="memo-wrap"><div class="memo-a4"><div class="memo-inner">

            <div class="memo-head">
                <img src="{{ asset('images/vignan-logo.png') }}" alt="Vignan's">
            </div>

            <div class="memo-title"><span>MEMORANDUM OF GRADES</span></div>

            <div class="memo-meta">
                <div class="pair"><div class="k">NAME</div><div>:</div><div class="v">{{ $student_info?->student_name }}</div></div>
                <div class="pair"><div class="k">MM NO.</div><div>:</div><div class="v">{{ $basic_info['mmno'] ?? '' }}</div></div>
                <div class="pair"><div class="k">PROGRAM</div><div>:</div><div class="v">{{$student_info?->program?->program_long_name}}</div></div>
                <div class="pair"><div class="k">REGISTER NUMBER</div><div>:</div><div class="v">{{ $student_info?->register_no }}</div></div>
                <div class="pair"><div class="k">ELECTIVE</div><div>:</div><div class="v">{{$student_info?->electives?->elective_long_name}}</div></div>
                <div class="pair"><div class="k">SEMESTER</div><div>:</div><div class="v">{{ ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'][($basic_info['s_sem'] ?? 0) - 1] ?? '' }}</div></div>
                <div class="pair"><div class="k">BATCH</div><div>:</div><div class="v">{{$student_info?->adm_batch_code}}</div></div>
                <div class="pair"><div class="k">MONTH &amp; YEAR</div><div>:</div><div class="v">{{ $basic_info['exam_mon_yyyy'] ?? '' }}</div></div>
            </div>

            <table class="memo-table">
                <thead>
                <tr>
                    <th>SL. NO</th><th>COURSE CODE</th><th>COURSE TITLE</th>
                    <th>F.A</th><th>S.A</th><th>T.M</th><th>CR</th><th>G.P</th><th>L.G</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($results as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $r->subcode }}</td>
                        <td class="title">{{ $r?->subject?->subname ?? '' }}</td>
                        <td>{{ $r->fa_earned }}</td>
                        <td>{{ $r->sa_earned }}</td>
                        <td>{{ $r->total_earned }}</td>
                        <td>{{ $r?->subject?->credits }}</td>
                        <td>{{ $r->grade_points }}</td>
                        <td>{{ $r->letter_grade }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:#888">No marks published</td></tr>
                @endforelse
                </tbody>
            </table>

            <div class="memo-summary">
                <div class="pair"><div>MAXIMUM MARKS</div><div>:</div><div>{{ $maxMarks }}</div></div>
                <div class="pair"><div>TOTAL MARKS</div><div>:</div><div>{{ $totalMarks }}</div></div>
                <div class="pair"><div>SGPA</div><div>:</div><div>{{  $basic_info['sgpa_perc'] == 'NA' ? '-' :  $basic_info['sgpa_perc'] }}</div></div>
                <div class="pair"><div>DIVISION</div><div>:</div><div>{{ $basic_info['division_name'] ?? '' }}</div></div>
            </div>

            <div class="memo-legend">
                <div class="pair"><div>F.A</div><div>:</div><div>Formative Assessment</div></div>
                <div class="pair"><div>S.A</div><div>:</div><div>Summative Assessment</div></div>
                <div class="pair"><div>T.M</div><div>:</div><div>Total Marks</div></div>
                <div class="pair"><div>CR</div><div>:</div><div>Credits</div></div>
                <div class="pair"><div>G.P</div><div>:</div><div>Grade Points</div></div>
                <div class="pair"><div>L.G</div><div>:</div><div>Letter Grade</div></div>
            </div>

            <div class="memo-sign">
                <div class="memo-date"><strong>Date:</strong>&nbsp;&nbsp; {{ $basic_info['results_mon_yyyy'] ?? '' }}</div>
                <div class="memo-signature">
                    <div class="signature-script">Approved</div>
                    <div>Deputy Controller of Examination</div>
                </div>
            </div>

        </div></div></div>

</body>
</html>
