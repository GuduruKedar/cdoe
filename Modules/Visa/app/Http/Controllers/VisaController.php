<?php

namespace Modules\Visa\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Visa\Models\TicketDetail;
use Modules\Visa\Models\TicketMaster;
use Modules\Visa\Services\TicketKpiService;

class VisaController extends Controller
{
    use RequiresLogin;

    /**
     * Display the Raise Ticket screen for students with 3 sections:
     * 1. 4 Variables / Counters: Raised (0), Pending (1), In Process (2), Resolved (3)
     * 2. Dynamic Form: Dropdowns for tkt_main_type & tkt_sub_type, Reason/Problem textarea
     * 3. Sample Rectangle Box container (recent tickets & status monitor)
     */
    public function ticketRaise(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);

        if ($menus === null && ! ($request->ajax() || $request->wantsJson())) {
            return view('errors.no-role', compact('user'));
        }

        $userCode = $user->usercode ?? session('user.usercode');

        // Look up student details
        $student = StudentRegistration::with('program')
            ->where('register_no', $userCode)
            ->first();

        $regNo = $userCode;

        // 1st Section: 4 variables (0=raised, 1=pending, 2=in process, 3=resolved)
        $statsRow = TicketDetail::where('regno', $regNo)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RAISED.' THEN 1 END) as raised,
                COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_PENDING.' THEN 1 END) as pending,
                COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_IN_PROCESS.' THEN 1 END) as in_process,
                COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RESOLVED.' THEN 1 END) as resolved
            ')
            ->first();

        $stats = [
            'total' => (int) ($statsRow?->total ?? 0),
            'raised' => (int) ($statsRow?->raised ?? 0),
            'pending' => (int) ($statsRow?->pending ?? 0),
            'in_process' => (int) ($statsRow?->in_process ?? 0),
            'resolved' => (int) ($statsRow?->resolved ?? 0),
            'to_reviews' => (int) ($statsRow?->pending ?? 0),
            'under_progress' => (int) ($statsRow?->in_process ?? 0),
        ];

        // 2nd Session: Distinct main query types from visa.ticket_master
        $mainTypes = TicketMaster::where('lock_status', 0)
            ->select('tkt_main_type')
            ->distinct()
            ->whereNotNull('tkt_main_type')
            ->pluck('tkt_main_type');

        // Pre-group sub types by main type for snappy client-side dynamic switching
        $groupedSubTypes = TicketMaster::where('lock_status', 0)
            ->select('tkt_head_id', 'tkt_main_type', 'tkt_sub_type')
            ->orderBy('sno')
            ->get()
            ->groupBy('tkt_main_type');

        // 3rd Section: Sample rectangle box displaying student's recent tickets
        $activeStatus = $request->query('status');
        $recentTicketsQuery = TicketDetail::with('ticketMaster')
            ->where('regno', $regNo)
            ->orderByDesc('tkt_raised_datetime');

        if ($activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all') {
            $recentTicketsQuery->where('std_tkt_status', (int) $activeStatus);
        }

        $recentTickets = $recentTicketsQuery->limit(25)->get();

        return view('visa::ticket_raise', compact(
            'user',
            'menus',
            'student',
            'stats',
            'mainTypes',
            'groupedSubTypes',
            'recentTickets',
            'activeStatus'
        ));
    }

    /**
     * AJAX endpoint to return sub-types for a selected main query type
     */
    public function getSubTypes(Request $request)
    {
        $mainType = $request->query('main_type');
        if (! $mainType) {
            return response()->json([]);
        }

        $subTypes = TicketMaster::where('tkt_main_type', $mainType)
            ->where('lock_status', 0)
            ->select('tkt_head_id', 'tkt_sub_type')
            ->orderBy('sno')
            ->get();

        return response()->json($subTypes);
    }

    /**
     * Store new student ticket
     */
    public function storeTicket(Request $request)
    {
        $user = $this->requireLogin();
        $userCode = $user->usercode ?? session('user.usercode');

        $request->validate([
            'tkt_main_type' => 'required|string',
            'tkt_head_id' => ['required', 'string', Rule::exists(TicketMaster::class, 'tkt_head_id')],
            'std_tkt_query' => 'required|string|max:500',
            'std_tkt_file' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,zip,txt,xls,xlsx,csv',
        ], [
            'tkt_main_type.required' => 'Please select a main query category.',
            'tkt_head_id.required' => 'Please select a specific query sub-type.',
            'std_tkt_query.required' => 'Please enter the reason or problem details.',
            'std_tkt_query.max' => 'The problem description may not exceed 500 characters.',
            'std_tkt_file.max' => 'Attachment file size must not exceed 10MB.',
            'std_tkt_file.mimes' => 'Attachment must be a PDF, image, Word document, or ZIP.',
        ]);

        $student = StudentRegistration::where('register_no', $userCode)->first();
        $regNo = $userCode;
        $appliNo = $student?->application_no ?? $userCode;

        // Generate clean unique Ticket ID: TKT-YYYYMMDD-XXXX
        $dateStr = now()->format('Ymd');
        $todayCount = TicketDetail::whereDate('tkt_raised_datetime', today())->count() + 1;
        $ticketId = 'TKT'.$dateStr.str_pad((string) $todayCount, 4, '0', STR_PAD_LEFT);

        while (TicketDetail::where('tkt_id', $ticketId)->exists()) {
            $todayCount++;
            $ticketId = 'TKT'.$dateStr.str_pad((string) $todayCount, 4, '0', STR_PAD_LEFT);
        }

        // Handle optional student file upload (kept strictly separate from staff files in DB and storage)
        $stdFilePath = null;
        $stdFileName = null;
        if ($request->hasFile('std_tkt_file')) {
            $file = $request->file('std_tkt_file');
            $stdFileName = $file->getClientOriginalName();
            $stdFilePath = $file->store('tickets/student_attachments', 'public');
        }

        $ticket = TicketDetail::create([
            'tkt_id' => $ticketId,
            'tkt_head_id' => $request->tkt_head_id,
            'regno' => $regNo,
            'applino' => $appliNo,
            'tkt_raised_datetime' => now(),
            'std_tkt_query' => trim($request->std_tkt_query),
            'std_tkt_status' => TicketDetail::STATUS_RAISED, // Database code 0 = Raised
            'std_tkt_file_path' => $stdFilePath,
            'std_tkt_file_name' => $stdFileName,
            'std_userid' => substr((string) $userCode, 0, 20),
            'std_ipaddress' => substr((string) $request->ip(), 0, 50),
            'std_timestamp' => now(),
        ]);

        ActivityLogger::log('VISA', 'RAISE_TICKET', "Student {$userCode} raised ticket {$ticketId}.", [
            'ticket_id' => $ticketId,
            'tkt_head_id' => $request->tkt_head_id,
            'regno' => $regNo,
            'status' => 'success',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ticket #{$ticketId} has been successfully raised!",
                'ticket_id' => $ticketId,
            ]);
        }

        return redirect()->route('visa.ticketraise')
            ->with('success', "Ticket #{$ticketId} has been successfully submitted! Our support team will review it shortly.");
    }

    /**
     * Display the Student Ticket Raise screen for Staff / Employees (visa/ticketriasebyemp).
     * Takes student register number. If exists, loads student info and opens ticket raise form on their behalf.
     */
    public function ticketRaiseByEmp(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);

        if ($menus === null && ! ($request->ajax() || $request->wantsJson())) {
            return view('errors.no-role', compact('user'));
        }

        $searchedRegNo = trim((string) $request->query('regno', old('student_regno', '')));
        $student = null;
        $searchPerformed = false;
        $studentNotFound = false;

        if ($searchedRegNo !== '') {
            $searchPerformed = true;
            $student = StudentRegistration::with('program')
                ->where(function ($q) use ($searchedRegNo) {
                    $q->where('register_no', $searchedRegNo)
                        ->orWhere('application_no', $searchedRegNo);
                })
                ->first();

            if (! $student) {
                $studentNotFound = true;
            }
        }

        $stats = [
            'total' => 0,
            'raised' => 0,
            'pending' => 0,
            'in_process' => 0,
            'resolved' => 0,
        ];
        $recentTickets = collect();
        $activeStatus = $request->query('status');

        if ($student) {
            $regNo = $student->register_no ?: $student->application_no;
            $statsRow = TicketDetail::where('regno', $regNo)
                ->selectRaw('
                    COUNT(*) as total,
                    COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RAISED.' THEN 1 END) as raised,
                    COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_PENDING.' THEN 1 END) as pending,
                    COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_IN_PROCESS.' THEN 1 END) as in_process,
                    COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RESOLVED.' THEN 1 END) as resolved
                ')
                ->first();

            $stats = [
                'total' => (int) ($statsRow?->total ?? 0),
                'raised' => (int) ($statsRow?->raised ?? 0),
                'pending' => (int) ($statsRow?->pending ?? 0),
                'in_process' => (int) ($statsRow?->in_process ?? 0),
                'resolved' => (int) ($statsRow?->resolved ?? 0),
            ];

            $recentTicketsQuery = TicketDetail::with('ticketMaster')
                ->where('regno', $regNo)
                ->orderByDesc('tkt_raised_datetime');

            if ($activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all') {
                $recentTicketsQuery->where('std_tkt_status', (int) $activeStatus);
            }

            $recentTickets = $recentTicketsQuery->limit(25)->get();
        }

        // Distinct main query types from visa.ticket_master
        $mainTypes = TicketMaster::where('lock_status', 0)
            ->select('tkt_main_type')
            ->distinct()
            ->whereNotNull('tkt_main_type')
            ->pluck('tkt_main_type');

        // Pre-group sub types
        $groupedSubTypes = TicketMaster::where('lock_status', 0)
            ->select('tkt_head_id', 'tkt_main_type', 'tkt_sub_type')
            ->orderBy('sno')
            ->get()
            ->groupBy('tkt_main_type');

        return view('visa::ticket_raise_by_emp', compact(
            'user',
            'menus',
            'searchedRegNo',
            'student',
            'searchPerformed',
            'studentNotFound',
            'stats',
            'mainTypes',
            'groupedSubTypes',
            'recentTickets',
            'activeStatus'
        ));
    }

    /**
     * Store new ticket raised by Staff/Employee on behalf of a student.
     */
    public function storeTicketByEmp(Request $request)
    {
        $user = $this->requireLogin();
        $userCode = $user->usercode ?? session('user.usercode');

        $request->validate([
            'student_regno' => 'required|string',
            'tkt_main_type' => 'required|string',
            'tkt_head_id' => ['required', 'string', Rule::exists(TicketMaster::class, 'tkt_head_id')],
            'std_tkt_query' => 'required|string|max:500',
            'std_tkt_file' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,zip,txt,xls,xlsx,csv',
        ], [
            'student_regno.required' => 'Student register number is required.',
            'tkt_main_type.required' => 'Please select a main query category.',
            'tkt_head_id.required' => 'Please select a specific query sub-type.',
            'std_tkt_query.required' => 'Please enter the reason or problem details.',
            'std_tkt_query.max' => 'The problem description may not exceed 500 characters.',
            'std_tkt_file.max' => 'Attachment file size must not exceed 10MB.',
            'std_tkt_file.mimes' => 'Attachment must be a PDF, image, Word document, or ZIP.',
        ]);

        $studentRegNo = trim((string) $request->student_regno);
        $student = StudentRegistration::where(function ($q) use ($studentRegNo) {
            $q->where('register_no', $studentRegNo)
                ->orWhere('application_no', $studentRegNo);
        })->first();

        if (! $student) {
            return redirect()->route('visa.ticketraisebyemp', ['regno' => $studentRegNo])
                ->withErrors(['student_regno' => "Student with register number '{$studentRegNo}' does not exist."])
                ->withInput();
        }

        $regNo = $student->register_no ?: $studentRegNo;
        $appliNo = $student->application_no ?: $regNo;

        // Generate clean unique Ticket ID: TKT-YYYYMMDD-XXXX
        $dateStr = now()->format('Ymd');
        $todayCount = TicketDetail::whereDate('tkt_raised_datetime', today())->count() + 1;
        $ticketId = 'TKT'.$dateStr.str_pad((string) $todayCount, 4, '0', STR_PAD_LEFT);

        while (TicketDetail::where('tkt_id', $ticketId)->exists()) {
            $todayCount++;
            $ticketId = 'TKT'.$dateStr.str_pad((string) $todayCount, 4, '0', STR_PAD_LEFT);
        }

        $stdFilePath = null;
        $stdFileName = null;
        if ($request->hasFile('std_tkt_file')) {
            $file = $request->file('std_tkt_file');
            $stdFileName = $file->getClientOriginalName();
            $stdFilePath = $file->store('tickets/student_attachments', 'public');
        }

        $ticket = TicketDetail::create([
            'tkt_id' => $ticketId,
            'tkt_head_id' => $request->tkt_head_id,
            'regno' => $regNo,
            'applino' => $appliNo,
            'tkt_raised_datetime' => now(),
            'std_tkt_query' => trim($request->std_tkt_query),
            'std_tkt_status' => TicketDetail::STATUS_RAISED,
            'std_tkt_file_path' => $stdFilePath,
            'std_tkt_file_name' => $stdFileName,
            'std_userid' => substr((string) $regNo, 0, 20),
            'assigned_empcode' => substr((string) $userCode, 0, 20),
            'std_ipaddress' => substr((string) $request->ip(), 0, 50),
            'std_timestamp' => now(),
        ]);

        ActivityLogger::log('VISA', 'RAISE_TICKET_BY_STAFF', "Staff {$userCode} raised ticket {$ticketId} on behalf of student {$regNo}.", [
            'ticket_id' => $ticketId,
            'staff_code' => $userCode,
            'student_regno' => $regNo,
            'tkt_head_id' => $request->tkt_head_id,
            'status' => 'success',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ticket #{$ticketId} has been successfully raised for student {$student->student_name} ({$regNo})!",
                'ticket_id' => $ticketId,
            ]);
        }

        return redirect()->route('visa.ticketraisebyemp', ['regno' => $regNo])
            ->with('success', "Ticket #{$ticketId} has been successfully raised on behalf of student {$student->student_name} ({$regNo})!");
    }

    /**
     * AJAX endpoint to lookup student details by register number or application number.
     */
    public function lookupStudent(Request $request)
    {
        $this->requireLogin();
        $regNo = trim((string) $request->query('regno'));

        if (! $regNo) {
            return response()->json(['found' => false, 'message' => 'Please provide a registration number.']);
        }

        $student = StudentRegistration::with('program')
            ->where(function ($q) use ($regNo) {
                $q->where('register_no', $regNo)
                    ->orWhere('application_no', $regNo);
            })
            ->first();

        if (! $student) {
            return response()->json(['found' => false, 'message' => "No student found matching '{$regNo}'."]);
        }

        return response()->json([
            'found' => true,
            'student' => [
                'register_no' => $student->register_no,
                'application_no' => $student->application_no,
                'student_name' => $student->student_name,
                'program_name' => $student->program?->program_name ?? 'N/A',
                'batch' => $student->adm_batch_code ?: ($student->admyear ? (string) $student->admyear : 'N/A'),
                'mobile' => $student->mobile ?: '—',
                'email' => $student->personal_email ?: ($student->ofc_mail ?: '—'),
            ],
        ]);
    }

    /**
     * Display Tickets Management for DSM / Staff
     */
    public function ticketsList(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);

        if ($menus === null && ! ($request->ajax() || $request->wantsJson())) {
            return view('errors.no-role', compact('user'));
        }

        $roleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));

        // If user is a student (Role 5), redirect to student's raise ticket screen
        if ($roleId === 5) {
            return redirect()->route('visa.ticketraise');
        }

        // Aggregate 4 stats for DSM side
        $statsRow = TicketDetail::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RAISED.' THEN 1 END) as raised,
            COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_PENDING.' THEN 1 END) as pending,
            COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_IN_PROCESS.' THEN 1 END) as in_process,
            COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RESOLVED.' THEN 1 END) as resolved
        ')->first();

        $stats = [
            'total' => (int) ($statsRow?->total ?? 0),
            'raised' => (int) ($statsRow?->raised ?? 0),
            'pending' => (int) ($statsRow?->pending ?? 0),
            'in_process' => (int) ($statsRow?->in_process ?? 0),
            'resolved' => (int) ($statsRow?->resolved ?? 0),
        ];

        // Query tickets — active tickets (Raised/Pending/In Process) first, Resolved last, then newest within each group
        $query = TicketDetail::with(['ticketMaster', 'student.program'])
            ->orderByRaw('CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RESOLVED.' THEN 1 ELSE 0 END ASC')
            ->orderByDesc('tkt_raised_datetime');

        $activeStatus = $request->query('status');
        if ($activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all') {
            $query->where('std_tkt_status', (int) $activeStatus);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('tkt_id', 'ILIKE', "%{$search}%")
                    ->orWhere('regno', 'ILIKE', "%{$search}%")
                    ->orWhere('applino', 'ILIKE', "%{$search}%")
                    ->orWhere('std_tkt_query', 'ILIKE', "%{$search}%");
            });
        }

        // Configurable & bounded per-page records count (cannot exceed total tickets)
        $totalTicketsCount = (clone $query)->count();
        $defaultPerPage = 15;
        $perPageInput = $request->query('per_page');

        if ($perPageInput !== null && $perPageInput !== '') {
            $requested = (int) $perPageInput;
            $maxAllowed = max(1, $totalTicketsCount);
            $perPage = max(1, min($requested, $maxAllowed));
        } else {
            $perPage = $defaultPerPage;
        }

        $tickets = $query->paginate($perPage)->withQueryString();

        return view('visa::tickets_list', compact(
            'user',
            'menus',
            'stats',
            'tickets',
            'activeStatus'
        ));
    }

    /**
     * Tickets KPI & AI Perceptions dashboard (director view).
     * - Executive one-line summary + health score
     * - KPI Dashboard tab: status, lifecycle timings, SLA, ageing, trend, category, staff, watchlist
     * - AI Perceptions tab: rule-based findings with recommended actions
     * - Period filter (?period=all|today|7|30|90) applies to cards, charts and table
     */
    public function ticketsKpi(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);

        if ($menus === null && ! ($request->ajax() || $request->wantsJson())) {
            return view('errors.no-role', compact('user'));
        }

        $roleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));

        // If user is a student (Role 5), redirect to student's raise ticket screen
        if ($roleId === 5) {
            return redirect()->route('visa.ticketraise');
        }

        // ---- Period filter ----
        $period = (string) $request->query('period', 'all');
        if (! in_array($period, ['all', 'today', '7', '30', '90'], true)) {
            $period = 'all';
        }
        $periodStart = match ($period) {
            'today' => now()->startOfDay(),
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            default => null,
        };

        // ---- Analytics dataset (one light row per ticket) ----
        $hasStage = $this->hasStageTimestamps();
        $toTs = fn ($v) => $v ? Carbon::parse($v)->getTimestamp() : null;

        $rows = TicketDetail::with(['ticketMaster', 'assignedEmployee', 'handlerEmployee'])
            ->get()
            ->map(function ($t) use ($hasStage, $toTs) {
                $status = (int) $t->std_tkt_status;
                $empTs = $toTs($t->emp_timestamp);

                // Without stage columns, emp_timestamp only tells us when the CURRENT status was set.
                $pendingAt = $hasStage ? $toTs($t->pending_at) : ($status === TicketDetail::STATUS_PENDING ? $empTs : null);
                $inProcAt = $hasStage ? $toTs($t->in_process_at) : ($status === TicketDetail::STATUS_IN_PROCESS ? $empTs : null);
                $resolvedAt = $status === TicketDetail::STATUS_RESOLVED
                    ? (($hasStage ? $toTs($t->resolved_at) : null) ?? $empTs)
                    : null;

                return [
                    'tkt_id' => (string) $t->tkt_id,
                    'regno' => (string) $t->regno,
                    'status' => $status,
                    'category' => $t->ticketMaster?->tkt_main_type,
                    'employee_code' => $t->emp_userid ?: $t->assigned_empcode,
                    'employee_name' => $t->handlerEmployee?->full_name ?? $t->assignedEmployee?->full_name,
                    'raised_at' => $toTs($t->tkt_raised_datetime),
                    'pending_at' => $pendingAt,
                    'in_process_at' => $inProcAt,
                    'resolved_at' => $resolvedAt,
                ];
            })
            ->all();

        $periodRows = $periodStart
            ? array_values(array_filter($rows, fn ($r) => $r['raised_at'] && $r['raised_at'] >= $periodStart->getTimestamp()))
            : $rows;

        $kpi = TicketKpiService::build($periodRows, $rows, now()->getTimestamp(), $hasStage);

        // Keep the variables the existing view parts use
        $stats = [
            'total' => $kpi['counts']['total'],
            'raised' => $kpi['counts']['raised'],
            'pending' => $kpi['counts']['pending'],
            'in_process' => $kpi['counts']['in_process'],
            'resolved' => $kpi['counts']['resolved'],
        ];
        $avgResolutionFormatted = $kpi['timing']['resolution']['avg_fmt'];

        // ---- Table ----
        $query = TicketDetail::with(['ticketMaster', 'student.program', 'assignedEmployee', 'handlerEmployee'])
            ->orderByDesc('tkt_raised_datetime');

        if ($periodStart) {
            $query->where('tkt_raised_datetime', '>=', $periodStart);
        }

        $activeStatus = $request->query('status');
        if ($activeStatus !== null && $activeStatus !== '' && $activeStatus !== 'all') {
            $query->where('std_tkt_status', (int) $activeStatus);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('tkt_id', 'ILIKE', "%{$search}%")
                    ->orWhere('regno', 'ILIKE', "%{$search}%")
                    ->orWhere('applino', 'ILIKE', "%{$search}%")
                    ->orWhere('assigned_empcode', 'ILIKE', "%{$search}%")
                    ->orWhere('emp_userid', 'ILIKE', "%{$search}%")
                    ->orWhere('std_tkt_query', 'ILIKE', "%{$search}%");
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        return view('visa::tickets_kpi', compact(
            'user',
            'menus',
            'stats',
            'tickets',
            'activeStatus',
            'avgResolutionFormatted',
            'kpi',
            'period'
        ));
    }

    /**
     * True when the pending_at / in_process_at / resolved_at columns exist
     * (added by the add_stage_timestamps migration).
     */
    protected function hasStageTimestamps(): bool
    {
        static $has = null;

        if ($has === null) {
            try {
                $model = new TicketDetail;
                $has = Schema::connection($model->getConnectionName())
                    ->hasColumns($model->getTable(), ['pending_at', 'in_process_at', 'resolved_at']);
            } catch (\Throwable $e) {
                $has = false;
            }
        }

        return $has;
    }

    /**
     * Root VISA hub redirect
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $roleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));

        if ($roleId === 5) {
            return redirect()->route('visa.ticketraise');
        }

        return redirect()->route('visa.ticketslist');
    }

    /**
     * Get ticket details (AJAX endpoint)
     * Auto-updates ticket status from 0 (Raised) to 1 (Pending) when viewed by DSM/Staff.
     */
    public function getTicketDetails(Request $request, string $ticketId)
    {
        $user = $this->requireLogin();
        $userCode = $user->usercode ?? session('user.usercode');
        $roleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));
        $isStaff = $roleId !== 5;
        $isReadOnly = $request->boolean('readonly');

        $ticket = TicketDetail::with(['ticketMaster', 'student.program', 'assignedEmployee', 'handlerEmployee'])
            ->where('tkt_id', $ticketId)
            ->firstOrFail();

        // If student, ensure ticket belongs to them
        if (! $isStaff && strcasecmp(trim((string) $ticket->regno), trim((string) $userCode)) !== 0) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $statusChanged = false;

        // If viewed by DSM/staff on review screen and status is 0 (Raised), automatically update to 1 (Pending)
        if ($isStaff && ! $isReadOnly && (int) $ticket->std_tkt_status === TicketDetail::STATUS_RAISED) {
            $ticket->update([
                'std_tkt_status' => TicketDetail::STATUS_PENDING,
                'emp_tkt_status' => TicketDetail::STATUS_PENDING,
                'assigned_empcode' => substr((string) $userCode, 0, 20),
                'emp_userid' => substr((string) $userCode, 0, 20),
                'emp_ipaddress' => substr((string) $request->ip(), 0, 50),
                'emp_timestamp' => now(),
            ]);

            // Stage tracking: first time staff opened the ticket
            if ($this->hasStageTimestamps() && empty($ticket->pending_at)) {
                $ticket->forceFill(['pending_at' => now()])->save();
            }

            $ticket->refresh();
            $statusChanged = true;

            ActivityLogger::log('VISA', 'VIEW_TICKET', "DSM {$userCode} viewed ticket {$ticketId}. Status automatically changed from Raised (0) to Pending (1).", [
                'ticket_id' => $ticketId,
                'old_status' => 0,
                'new_status' => 1,
            ]);
        }

        // Return current stats counts if status changed
        $stats = null;
        if ($statusChanged) {
            $statsRow = TicketDetail::selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RAISED.' THEN 1 END) as raised,
                COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_PENDING.' THEN 1 END) as pending,
                COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_IN_PROCESS.' THEN 1 END) as in_process,
                COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RESOLVED.' THEN 1 END) as resolved
            ')->first();

            $stats = [
                'total' => (int) ($statsRow?->total ?? 0),
                'raised' => (int) ($statsRow?->raised ?? 0),
                'pending' => (int) ($statsRow?->pending ?? 0),
                'in_process' => (int) ($statsRow?->in_process ?? 0),
                'resolved' => (int) ($statsRow?->resolved ?? 0),
            ];
        }

        return response()->json([
            'success' => true,
            'ticket' => [
                'tkt_id' => $ticket->tkt_id,
                'tkt_head_id' => $ticket->tkt_head_id,
                'regno' => $ticket->regno,
                'applino' => $ticket->applino,
                'student_name' => $ticket->student?->student_name ?? $ticket->student?->name ?? 'Student',
                'program' => $ticket->student?->program?->program_name ?? 'N/A',
                'category' => $ticket->ticketMaster?->tkt_main_type ?? 'N/A',
                'sub_type' => $ticket->ticketMaster?->tkt_sub_type ?? 'N/A',
                'query' => $ticket->std_tkt_query,
                'std_tkt_file_name' => $ticket->std_tkt_file_name,
                'std_tkt_file_path' => $ticket->std_tkt_file_path,
                'has_std_file' => ! empty($ticket->std_tkt_file_path),
                'std_file_url' => ! empty($ticket->std_tkt_file_path) ? route('visa.ticket.download_student', $ticket->tkt_id) : null,
                'raised_date' => $ticket->tkt_raised_datetime ? Carbon::parse($ticket->tkt_raised_datetime)->format('d M Y, h:i A') : '—',
                'std_tkt_status' => (int) $ticket->std_tkt_status,
                'status_label' => $ticket->status_label,
                'status_class' => $ticket->status_class,
                'duration_formatted' => $ticket->duration_formatted,
                'is_resolved' => $ticket->is_resolved,
                'assigned_emp_code' => $ticket->assigned_empcode ?: '—',
                'assigned_emp_name' => $ticket->assignedEmployee?->full_name ?? ($ticket->assigned_empcode ? 'Emp: '.$ticket->assigned_empcode : 'Unassigned'),
                'handler_emp_code' => $ticket->emp_userid ?: '—',
                'handler_emp_name' => $ticket->handlerEmployee?->full_name ?? ($ticket->emp_userid ? 'Emp: '.$ticket->emp_userid : 'Unassigned'),
                'emp_tkt_comment' => $ticket->emp_tkt_comment,
                'emp_tkt_file_name' => $ticket->emp_tkt_file_name,
                'emp_tkt_file_path' => $ticket->emp_tkt_file_path,
                'has_file' => ! empty($ticket->emp_tkt_file_path),
                'file_url' => ! empty($ticket->emp_tkt_file_path) ? route('visa.ticket.download', $ticket->tkt_id) : null,
                'emp_timestamp' => $ticket->emp_timestamp ? Carbon::parse($ticket->emp_timestamp)->format('d M Y, h:i A') : null,
                'emp_userid' => $ticket->emp_userid,
            ],
            'status_changed' => $statusChanged,
            'stats' => $stats,
        ]);
    }

    /**
     * Update Ticket Status (DSM action)
     * For 1 (Pending) and 2 (In Process): No extra inputs asked.
     * For 3 (Resolved): Textarea required, file upload optional.
     */
    public function updateTicketStatus(Request $request, string $ticketId)
    {
        $user = $this->requireLogin();
        $userCode = $user->usercode ?? session('user.usercode');
        $roleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));

        if ($roleId === 5) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $ticket = TicketDetail::where('tkt_id', $ticketId)->firstOrFail();

        // Once a ticket is resolved, its status cannot be changed back
        if ((int) $ticket->std_tkt_status === TicketDetail::STATUS_RESOLVED) {
            return response()->json([
                'success' => false,
                'message' => 'This ticket has already been resolved and closed. Status updates cannot be reversed.',
            ], 422);
        }

        $rules = [
            'status' => 'required|integer|in:1,2,3',
            'emp_tkt_comment' => 'nullable|string|max:2000',
            'emp_tkt_file' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,zip,txt,xls,xlsx,csv',
        ];

        $newStatus = (int) $request->input('status');

        // For status 3 (Resolved), text input is required
        if ($newStatus === TicketDetail::STATUS_RESOLVED) {
            $rules['emp_tkt_comment'] = 'required|string|min:3|max:2000';
        }

        $messages = [
            'status.required' => 'Please select a status.',
            'emp_tkt_comment.required' => 'Resolution remarks/comment is required when resolving a ticket.',
            'emp_tkt_comment.min' => 'Resolution remarks should be at least 3 characters.',
            'emp_tkt_file.max' => 'Attachment file size must not exceed 10MB.',
            'emp_tkt_file.mimes' => 'Attachment must be a PDF, image, Word document, or ZIP.',
        ];

        $request->validate($rules, $messages);

        $updateData = [
            'std_tkt_status' => $newStatus,
            'emp_tkt_status' => $newStatus,
            'assigned_empcode' => substr((string) $userCode, 0, 20),
            'emp_userid' => substr((string) $userCode, 0, 20),
            'emp_ipaddress' => substr((string) $request->ip(), 0, 50),
            'emp_timestamp' => now(),
        ];

        // Status 3 handles comment and optional file
        if ($newStatus === TicketDetail::STATUS_RESOLVED) {
            $updateData['emp_tkt_comment'] = trim((string) $request->emp_tkt_comment);

            if ($request->hasFile('emp_tkt_file')) {
                $file = $request->file('emp_tkt_file');
                $fileName = $file->getClientOriginalName();
                $filePath = $file->store('tickets/resolutions', 'public');

                $updateData['emp_tkt_file_path'] = $filePath;
                $updateData['emp_tkt_file_name'] = $fileName;
            }
        }

        // Stage tracking (kept separate from emp_timestamp, which is overwritten on every change)
        $stageData = [];
        if ($this->hasStageTimestamps()) {
            $oldStatus = (int) $ticket->std_tkt_status;

            if (empty($ticket->pending_at)) {
                $stageData['pending_at'] = now();          // first staff response
            }
            if ($newStatus === TicketDetail::STATUS_IN_PROCESS && empty($ticket->in_process_at)) {
                $stageData['in_process_at'] = now();
            }
            if ($newStatus === TicketDetail::STATUS_RESOLVED) {
                if ($oldStatus !== TicketDetail::STATUS_RESOLVED || empty($ticket->resolved_at)) {
                    $stageData['resolved_at'] = now();     // keep original time when only remarks are edited
                }
            } elseif (! empty($ticket->resolved_at)) {
                $stageData['resolved_at'] = null;          // ticket was re-opened
            }
        }

        $ticket->update($updateData);

        if (! empty($stageData)) {
            $ticket->forceFill($stageData)->save();
        }

        ActivityLogger::log('VISA', 'UPDATE_TICKET_STATUS', "DSM {$userCode} updated ticket {$ticketId} to status {$newStatus}.", [
            'ticket_id' => $ticketId,
            'status' => $newStatus,
            'comment' => $updateData['emp_tkt_comment'] ?? null,
        ]);

        // Get updated counts for snappy UI refresh
        $statsRow = TicketDetail::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RAISED.' THEN 1 END) as raised,
            COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_PENDING.' THEN 1 END) as pending,
            COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_IN_PROCESS.' THEN 1 END) as in_process,
            COUNT(CASE WHEN std_tkt_status = '.TicketDetail::STATUS_RESOLVED.' THEN 1 END) as resolved
        ')->first();

        $stats = [
            'total' => (int) ($statsRow?->total ?? 0),
            'raised' => (int) ($statsRow?->raised ?? 0),
            'pending' => (int) ($statsRow?->pending ?? 0),
            'in_process' => (int) ($statsRow?->in_process ?? 0),
            'resolved' => (int) ($statsRow?->resolved ?? 0),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ticket #{$ticketId} has been updated to {$ticket->status_label}.",
                'ticket_id' => $ticketId,
                'status' => $newStatus,
                'status_label' => $ticket->status_label,
                'status_class' => $ticket->status_class,
                'stats' => $stats,
            ]);
        }

        return redirect()->back()->with('success', "Ticket #{$ticketId} has been updated to {$ticket->status_label}.");
    }

    /**
     * Secure download of ticket resolution file (uploaded by staff)
     */
    public function downloadTicketFile(string $ticketId)
    {
        $user = $this->requireLogin();
        $userCode = $user->usercode ?? session('user.usercode');
        $roleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));

        $ticket = TicketDetail::where('tkt_id', $ticketId)->firstOrFail();

        // Student can only download their own ticket files
        if ($roleId === 5 && strcasecmp(trim((string) $ticket->regno), trim((string) $userCode)) !== 0) {
            abort(403, 'Unauthorized access to this attachment.');
        }

        if (! $ticket->emp_tkt_file_path || ! Storage::disk('public')->exists($ticket->emp_tkt_file_path)) {
            abort(404, 'File not found or has been removed.');
        }

        return Storage::disk('public')->download(
            $ticket->emp_tkt_file_path,
            $ticket->emp_tkt_file_name ?: basename($ticket->emp_tkt_file_path)
        );
    }

    /**
     * Secure download of student ticket attachment (uploaded by student)
     */
    public function downloadStudentFile(string $ticketId)
    {
        $user = $this->requireLogin();
        $userCode = $user->usercode ?? session('user.usercode');
        $roleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));

        $ticket = TicketDetail::where('tkt_id', $ticketId)->firstOrFail();

        // Student can only download their own ticket files
        if ($roleId === 5 && strcasecmp(trim((string) $ticket->regno), trim((string) $userCode)) !== 0) {
            abort(403, 'Unauthorized access to this attachment.');
        }

        if (! $ticket->std_tkt_file_path || ! Storage::disk('public')->exists($ticket->std_tkt_file_path)) {
            abort(404, 'File not found or has been removed.');
        }

        return Storage::disk('public')->download(
            $ticket->std_tkt_file_path,
            $ticket->std_tkt_file_name ?: basename($ticket->std_tkt_file_path)
        );
    }

    /**
     * Delete Ticket
     * Allows a student to delete their own ticket (and staff to delete as needed).
     * Cleans up any attached storage file and logs activity.
     */
    public function deleteTicket(Request $request, string $ticketId)
    {
        $user = $this->requireLogin();
        $userCode = $user->usercode ?? session('user.usercode');
        $roleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));
        $isStaff = $roleId !== 5;

        $ticket = TicketDetail::where('tkt_id', $ticketId)->firstOrFail();

        // Student can only delete their own tickets
        if (! $isStaff && strcasecmp(trim((string) $ticket->regno), trim((string) $userCode)) !== 0) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: You can only delete your own tickets.'], 403);
            }

            return redirect()->back()->with('error', 'Unauthorized: You can only delete your own tickets.');
        }

        // Clean up staff uploaded resolution file
        if ($ticket->emp_tkt_file_path && Storage::disk('public')->exists($ticket->emp_tkt_file_path)) {
            Storage::disk('public')->delete($ticket->emp_tkt_file_path);
        }

        // Clean up student uploaded attachment file
        if ($ticket->std_tkt_file_path && Storage::disk('public')->exists($ticket->std_tkt_file_path)) {
            Storage::disk('public')->delete($ticket->std_tkt_file_path);
        }

        $ticket->delete();

        ActivityLogger::log('VISA', 'DELETE_TICKET', "User {$userCode} deleted ticket {$ticketId}.", [
            'ticket_id' => $ticketId,
            'role_id' => $roleId,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ticket #{$ticketId} was deleted successfully.",
                'ticket_id' => $ticketId,
            ]);
        }

        return redirect()->route('visa.ticketraise')->with('success', "Ticket #{$ticketId} was deleted successfully.");
    }
}
