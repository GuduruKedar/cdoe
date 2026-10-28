<?php

namespace Modules\Visa\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Visa\Models\TicketDetail;
use Modules\Visa\Models\TicketMaster;

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
        ], [
            'tkt_main_type.required' => 'Please select a main query category.',
            'tkt_head_id.required' => 'Please select a specific query sub-type.',
            'std_tkt_query.required' => 'Please enter the reason or problem details.',
            'std_tkt_query.max' => 'The problem description may not exceed 500 characters.',
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

        $ticket = TicketDetail::create([
            'tkt_id' => $ticketId,
            'tkt_head_id' => $request->tkt_head_id,
            'regno' => $regNo,
            'applino' => $appliNo,
            'tkt_raised_datetime' => now(),
            'std_tkt_query' => trim($request->std_tkt_query),
            'std_tkt_status' => TicketDetail::STATUS_RAISED, // Database code 0 = Raised
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

        $tickets = $query->paginate(15)->withQueryString();

        return view('visa::tickets_list', compact(
            'user',
            'menus',
            'stats',
            'tickets',
            'activeStatus'
        ));
    }

    /**
     * Display Tickets KPI & Resolution Metrics
     * Shows:
     * 1. 4 Cards for Raised (0), Pending (1), In Process (2), Resolved (3)
     * 2. Key Metrics: Total Tickets, Avg Resolution Time, Resolution Rate
     * 3. Comprehensive Tickets Table with Raised -> Solved Time Difference
     * 4. Interactive Row Click & Modal to view full ticket details
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

        // 1. The 4 Status Variables (Raised, Pending, In Process, Resolved)
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

        // 2. Average Resolution Time across resolved tickets
        $avgSecRow = TicketDetail::where('std_tkt_status', TicketDetail::STATUS_RESOLVED)
            ->whereNotNull('tkt_raised_datetime')
            ->whereNotNull('emp_timestamp')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (emp_timestamp - tkt_raised_datetime))) as avg_sec')
            ->first();

        $avgSec = (int) round($avgSecRow?->avg_sec ?? 0);
        $avgResolutionFormatted = $this->formatDurationHuman($avgSec);

        // 3. Average Pending Time across pending / open tickets
        $avgPendingRow = TicketDetail::where('std_tkt_status', TicketDetail::STATUS_PENDING)
            ->whereNotNull('tkt_raised_datetime')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (NOW() - tkt_raised_datetime))) as avg_sec')
            ->first();
        $avgPendingSec = (int) round($avgPendingRow?->avg_sec ?? 0);

        if ($avgPendingSec <= 0) {
            $avgOpenRow = TicketDetail::where('std_tkt_status', '!=', TicketDetail::STATUS_RESOLVED)
                ->whereNotNull('tkt_raised_datetime')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (NOW() - tkt_raised_datetime))) as avg_sec')
                ->first();
            $avgPendingSec = (int) round($avgOpenRow?->avg_sec ?? 0);
        }

        $avgPendingFormatted = $this->formatDurationHuman($avgPendingSec);

        // 4. SLA Compliance Rate (resolved within 24 hours target)
        $totalResolved = $stats['resolved'] ?? 0;
        $withinSla = TicketDetail::where('std_tkt_status', TicketDetail::STATUS_RESOLVED)
            ->whereNotNull('tkt_raised_datetime')
            ->whereNotNull('emp_timestamp')
            ->whereRaw('EXTRACT(EPOCH FROM (emp_timestamp - tkt_raised_datetime)) <= 86400')
            ->count();
        $slaRate = $totalResolved > 0 ? round(($withinSla / $totalResolved) * 100) : 100;

        // 5. Daily Activity & Overdue Counters
        $resolvedToday = TicketDetail::where('std_tkt_status', TicketDetail::STATUS_RESOLVED)
            ->whereNotNull('emp_timestamp')
            ->whereDate('emp_timestamp', today())
            ->count();
        $raisedToday = TicketDetail::whereNotNull('tkt_raised_datetime')
            ->whereDate('tkt_raised_datetime', today())
            ->count();
        $overdueCount = TicketDetail::where('std_tkt_status', '!=', TicketDetail::STATUS_RESOLVED)
            ->whereNotNull('tkt_raised_datetime')
            ->whereRaw('EXTRACT(EPOCH FROM (NOW() - tkt_raised_datetime)) > 86400')
            ->count();

        // 6. Query tickets with relationships
        $query = TicketDetail::with(['ticketMaster', 'student.program', 'assignedEmployee', 'handlerEmployee'])
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
            'avgPendingFormatted',
            'slaRate',
            'resolvedToday',
            'raisedToday',
            'overdueCount'
        ));
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

        $ticket->update($updateData);

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
     * Secure download of ticket resolution file
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

        // Clean up any uploaded resolution file
        if ($ticket->emp_tkt_file_path && Storage::disk('public')->exists($ticket->emp_tkt_file_path)) {
            Storage::disk('public')->delete($ticket->emp_tkt_file_path);
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

    /**
     * Format duration seconds into readable human string: e.g. "24 mns", "4 days 20 mns"
     */
    protected function formatDurationHuman(int $seconds): string
    {
        if ($seconds <= 0) {
            return '—';
        }

        if ($seconds < 60) {
            return $seconds.'s';
        }

        $days = (int) floor($seconds / 86400);
        $rem = $seconds % 86400;
        $hours = (int) floor($rem / 3600);
        $mins = (int) floor(($rem % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = $days.' '.($days === 1 ? 'day' : 'days');
        }
        if ($hours > 0) {
            $parts[] = $hours.' '.($hours === 1 ? 'hr' : 'hrs');
        }
        if ($mins > 0 || empty($parts)) {
            $parts[] = $mins.' mns';
        }

        return implode(' ', $parts);
    }
}
