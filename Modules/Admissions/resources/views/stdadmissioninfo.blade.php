@extends('layouts.app')

@section('page_title', 'Admission Info')

@section('content')

    @if (!$student)
        <div class="ss-card" style="border-left: 4px solid #ef4444;">
            <div style="padding: 26px 24px;">
                <h3 style="color:#b91c1c; margin:0 0 8px; font-size:18px;">
                    <i class="bi bi-exclamation-triangle me-2"></i>No Record Found
                </h3>
                <p style="color:#64748b; margin:0; font-size:14px;">
                    No admission record found for register number
                    <strong style="color:#1e293b;">{{ $registerNo }}</strong>.
                </p>
            </div>
        </div>
    @else

        {{-- ================= HERO / STUDENT IDENTITY CARD ================= --}}
        <div class="ss-card ss-hero">
            <div class="ss-hero-avatar">
                {{ strtoupper(substr($student->student_name, 0, 1)) }}
            </div>
            <div class="ss-hero-body">
                <div class="ss-hero-name">{{ $student->student_name }}</div>
                <div class="ss-hero-meta">
                    <span class="ss-badge-reg">Register No: {{ $student->register_no }}</span>
                    <span class="ss-badge-regu">Enrollment: {{ $student->enrollment_no ?? '-' }}</span>
                    <span class="ss-badge-status {{ $student->std_status == 'Active' ? 'is-active' : 'is-inactive' }}">
                        {{ $student->std_status ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ================= PERSONAL INFORMATION ================= --}}
        <div class="ss-card">
            <div class="ss-section-title" style="padding: 16px 22px 12px;">
                <i class="bi bi-person-circle me-2"></i>Personal Information
            </div>
            <div class="ss-info-grid">
                <div class="ss-row"><span>Application No</span><b>{{ $student->application_no ?? '-' }}</b></div>
                <div class="ss-row"><span>Enrollment No</span><b>{{ $student->enrollment_no ?? '-' }}</b></div>
                <div class="ss-row"><span>Register No</span><b>{{ $student->register_no ?? '-' }}</b></div>
                <div class="ss-row"><span>Student Name</span><b>{{ $student->student_name ?? '-' }}</b></div>
                <div class="ss-row"><span>Personal Email</span><b>{{ $student->personal_email ?? '-' }}</b></div>
                <div class="ss-row"><span>Official Mail</span><b>{{ $student->ofc_mail ?? '-' }}</b></div>
                <div class="ss-row"><span>Date of Birth</span><b>{{ $student->dob ?? '-' }}</b></div>
                <div class="ss-row"><span>Gender</span><b>{{ $student->gender ?? '-' }}</b></div>
                <div class="ss-row"><span>Mobile</span><b>{{ $student->mobile ?? '-' }}</b></div>
                <div class="ss-row"><span>Aadhar No</span><b>{{ $student->aadhar_no ?? '-' }}</b></div>
                <div class="ss-row"><span>ABC ID</span><b>{{ $student->abc_id ?? '-' }}</b></div>
                <div class="ss-row"><span>DEB ID</span><b>{{ $student->deb_id ?? '-' }}</b></div>
            </div>
        </div>

        {{-- ================= ACADEMIC INFORMATION ================= --}}
        <div class="ss-card">
            <div class="ss-section-title" style="padding: 16px 22px 12px;">
                <i class="bi bi-mortarboard me-2"></i>Academic Information
            </div>
            <div class="ss-info-grid">
                <div class="ss-row"><span>Date of Joining</span><b>{{ $student->doj ?? '-' }}</b></div>
                <div class="ss-row"><span>Regulation</span><b>{{ $student->regulation ?? '-' }}</b></div>
                <div class="ss-row"><span>Program</span><b>{{ $student->program->program_code ?? '-' }}</b></div>
                <div class="ss-row"><span>Elective</span><b>{{ $student->electives->elective_short_name ?? '-' }}</b></div>
                <div class="ss-row"><span>Admission Year</span><b>{{ $student->admyear ?? '-' }}</b></div>
                <div class="ss-row"><span>Admission Batch</span><b>{{ $student->adm_batch_code ?? '-' }}</b></div>
                <div class="ss-row"><span>Fee ID</span><b>{{ $student->feeid ?? '-' }}</b></div>
                <div class="ss-row"><span>Student Type</span><b>{{ $student->std_type ?? '-' }}</b></div>
                <div class="ss-row"><span>Student Status</span><b>{{ $student->std_status ?? '-' }}</b></div>
                <div class="ss-row"><span>Lock Status</span><b>{{ $student->lock_status ?? '-' }}</b></div>
            </div>
        </div>

        {{-- ================= AUDIT INFORMATION ================= --}}
        <div class="ss-card">
            <div class="ss-section-title" style="padding: 16px 22px 12px;">
                <i class="bi bi-clock-history me-2"></i>Audit Information
            </div>
            <div class="ss-info-grid">
                <div class="ss-row"><span>Log User</span><b>{{ $student->log_userid ?? '-' }}</b></div>
                <div class="ss-row"><span>Log IP</span><b>{{ $student->log_ipaddress ?? '-' }}</b></div>
                <div class="ss-row"><span>Log Timestamp</span><b>{{ $student->log_timestamp ?? '—' }}</b></div>
            </div>
        </div>

    @endif

@endsection


{{-- ================= STYLES — matching stdsearch.blade.php ================= --}}
@push('styles')
    <style>
        /* ---- Card (same as stdsearch) ---- */
        .ss-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
            color: var(--text-secondary);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        /* ---- Hero / Identity Banner ---- */
        .ss-hero {
            display: flex;
            align-items: center;
            gap: 22px;
            padding: 26px 28px;
            background: var(--bg-card);
            border-left: 4px solid var(--theme-primary);
        }
        .ss-hero-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--theme-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 6px 16px var(--theme-glow);
        }
        .ss-hero-body { flex: 1; }
        .ss-hero-name {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .ss-hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        /* ---- Badges ---- */
        .ss-badge-reg {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            background: var(--bg-elevated);
            color: var(--text-secondary);
            border: 1px solid var(--border-subtle);
            font-weight: 600;
            font-size: 12px;
        }
        .ss-badge-regu {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 30px;
            background: var(--bg-elevated);
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 700;
            border: 1px solid var(--border-subtle);
        }
        .ss-badge-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .2px;
        }
        .ss-badge-status.is-active {
            background: rgba(34, 197, 94, 0.15);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        html.dark .ss-badge-status.is-active {
            color: #4ade80;
            border-color: rgba(74, 222, 128, 0.35);
        }
        .ss-badge-status.is-inactive {
            background: rgba(239, 68, 68, 0.15);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        html.dark .ss-badge-status.is-inactive {
            color: #f87171;
            border-color: rgba(248, 113, 113, 0.35);
        }

        /* ---- Section Title ---- */
        .ss-section-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--theme-primary);
            border-bottom: 1px solid var(--border-subtle);
            background: var(--bg-elevated);
        }

        /* ---- Info grid ---- */
        .ss-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 0 26px;
            padding: 6px 22px 18px;
        }
        .ss-row {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px dashed var(--border-subtle);
            font-size: 13.5px;
        }
        .ss-row:last-child { border-bottom: none; }
        .ss-row span {
            color: var(--text-muted);
            font-weight: 500;
        }
        .ss-row b {
            color: var(--text-primary);
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        /* ---- Responsive ---- */
        @media (max-width: 640px) {
            .ss-hero { flex-direction: column; text-align: center; }
            .ss-hero-meta { justify-content: center; }
            .ss-row { flex-direction: column; align-items: flex-start; }
            .ss-row b { text-align: left; }
        }
    </style>
@endpush
