<?php

namespace Modules\Admissions\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Admissions\Models\StudentRegistration;

class StudentStatusUpdateController extends Controller
{
    use RequiresLogin;

    /**
     * Allowed student statuses with metadata.
     *
     * @var array<string, array{label: string, badge_class: string, icon: string, description: string}>
     */
    protected array $statuses = [
        'Active / Admitted' => [
            'label' => 'Active / Admitted',
            'badge_class' => 'badge-status-admitted',
            'icon' => 'bi-check-circle-fill',
            'description' => 'Active student in good standing',
        ],
        'Debarred' => [
            'label' => 'Debarred',
            'badge_class' => 'badge-status-debarred',
            'icon' => 'bi-slash-circle-fill',
            'description' => 'Debarred due to examination or disciplinary misconduct',
        ],
        'Suspended' => [
            'label' => 'Suspended',
            'badge_class' => 'badge-status-suspended',
            'icon' => 'bi-pause-circle-fill',
            'description' => 'Temporarily suspended pending administrative review',
        ],
        'Semester Break' => [
            'label' => 'Semester Break',
            'badge_class' => 'badge-status-semester-break',
            'icon' => 'bi-calendar2-range-fill',
            'description' => 'Approved temporary break between academic semesters',
        ],
        'Study Break' => [
            'label' => 'Study Break',
            'badge_class' => 'badge-status-study-break',
            'icon' => 'bi-book-half',
            'description' => 'Official leave of absence or extended study break',
        ],
        'Fee Blocked' => [
            'label' => 'Fee Blocked',
            'badge_class' => 'badge-status-fee-blocked',
            'icon' => 'bi-lock-fill',
            'description' => 'Access restricted due to outstanding fee dues',
        ],
    ];

    /**
     * Normalize status string into one of the 6 canonical statuses.
     */
    public static function normalizeStatus(?string $status): string
    {
        $status = trim((string) $status);
        if ($status === '' || strcasecmp($status, 'Admitted') === 0 || strcasecmp($status, 'Active') === 0 || strcasecmp($status, 'Active / Admitted') === 0) {
            return 'Active / Admitted';
        }
        if (strcasecmp($status, 'Debarr') === 0 || strcasecmp($status, 'Debarred') === 0) {
            return 'Debarred';
        }
        if (strcasecmp($status, 'Suspend') === 0 || strcasecmp($status, 'Suspended') === 0) {
            return 'Suspended';
        }
        if (strcasecmp($status, 'Semester break') === 0 || strcasecmp($status, 'Semester Break') === 0) {
            return 'Semester Break';
        }
        if (strcasecmp($status, 'Study break') === 0 || strcasecmp($status, 'Study Break') === 0) {
            return 'Study Break';
        }
        if (strcasecmp($status, 'Fee blocked') === 0 || strcasecmp($status, 'Fee Blocked') === 0) {
            return 'Fee Blocked';
        }

        return $status;
    }

    /**
     * Display the Student Status Update page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $searchedRegNo = trim((string) $request->input('regno', ''));
        $student = null;
        $studentNotFound = false;

        if ($searchedRegNo !== '') {
            $student = StudentRegistration::with('program')
                ->where(function ($query) use ($searchedRegNo) {
                    $query->where('register_no', $searchedRegNo)
                        ->orWhere('application_no', $searchedRegNo)
                        ->orWhere('enrollment_no', $searchedRegNo);
                })
                ->first();

            if (! $student) {
                // Case-insensitive fallback
                $student = StudentRegistration::with('program')
                    ->whereRaw('LOWER(register_no) = ?', [strtolower($searchedRegNo)])
                    ->orWhereRaw('LOWER(application_no) = ?', [strtolower($searchedRegNo)])
                    ->first();
            }

            if (! $student) {
                $studentNotFound = true;
            } else {
                // Add to past 10 searched students history in session
                $this->addToSearchHistory($student);
            }
        }

        // Get past searched students history (limit 10)
        $searchedHistory = $this->getSearchHistory();

        return view('admissions::stdstatusupdate', [
            'user' => $user,
            'menus' => $menus,
            'searchedRegNo' => $searchedRegNo,
            'student' => $student,
            'studentNotFound' => $studentNotFound,
            'statuses' => $this->statuses,
            'searchedHistory' => $searchedHistory,
        ]);
    }

    /**
     * Update the student status.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $this->requireLogin();

        $request->validate([
            'application_no' => 'required|string',
            'new_status' => 'required|string',
            'remarks' => 'nullable|string|max:500',
        ]);

        $student = StudentRegistration::where('application_no', $request->input('application_no'))
            ->orWhere('register_no', $request->input('application_no'))
            ->firstOrFail();

        $oldStatus = self::normalizeStatus($student->std_status);
        $newStatus = trim($request->input('new_status'));
        $normalizedStatus = self::normalizeStatus($newStatus);
        $remarks = trim((string) $request->input('remarks', ''));

        // Validate that new status is recognized
        if (! array_key_exists($normalizedStatus, $this->statuses)) {
            return back()->with('error', 'Invalid student status selected. Please choose a valid status.');
        }

        // Update student record in database
        $student->std_status = $normalizedStatus;
        $student->log_userid = $user->usercode ?? session('user.usercode', 'SYSTEM');
        $student->log_ipaddress = $request->ip();
        $student->log_timestamp = now();
        $student->save();

        // Update session search history entry if present
        $this->updateSearchHistoryStatus($student->register_no ?: $student->application_no, $normalizedStatus);

        // Record in activity logs
        ActivityLogger::log(
            'ADMISSIONS',
            'STATUS_UPDATE',
            "Changed status for {$student->student_name} ({$student->register_no}) from '{$oldStatus}' to '{$normalizedStatus}'.".($remarks !== '' ? " Remarks: {$remarks}" : ''),
            [
                'payload' => [
                    'application_no' => $student->application_no,
                    'register_no' => $student->register_no,
                    'student_name' => $student->student_name,
                    'old_status' => $oldStatus,
                    'new_status' => $normalizedStatus,
                    'remarks' => $remarks,
                ],
                'user_code' => $user->usercode ?? session('user.usercode'),
                'user_name' => $user->username ?? session('user.username'),
            ]
        );

        $lookupKey = $student->register_no ?: $student->application_no;

        return redirect()
            ->route('admissions.stdstatusupdate', ['regno' => $lookupKey])
            ->with('success', "Status successfully updated to '{$normalizedStatus}' for {$student->student_name} ({$lookupKey}).");
    }

    /**
     * Quick AJAX lookup endpoint for instant autocomplete suggestions.
     */
    public function lookup(Request $request): JsonResponse
    {
        $this->requireLogin();

        $query = trim((string) $request->input('q', ''));
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = StudentRegistration::with('program')
            ->where(function ($q) use ($query) {
                $q->where('register_no', 'ILIKE', "%{$query}%")
                    ->orWhere('application_no', 'ILIKE', "%{$query}%")
                    ->orWhere('student_name', 'ILIKE', "%{$query}%")
                    ->orWhere('mobile', 'ILIKE', "%{$query}%")
                    ->orWhere('personal_email', 'ILIKE', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($s) {
                return [
                    'application_no' => $s->application_no,
                    'register_no' => $s->register_no,
                    'student_name' => $s->student_name,
                    'mobile' => $s->mobile,
                    'personal_email' => $s->personal_email,
                    'std_status' => self::normalizeStatus($s->std_status),
                    'program_name' => $s->program?->program_name ?? 'N/A',
                    'batch' => $s->adm_batch_code ?: ($s->admyear ? (string) $s->admyear : '—'),
                ];
            });

        return response()->json(['results' => $results]);
    }

    /**
     * Add student to session search history.
     */
    private function addToSearchHistory(StudentRegistration $student): void
    {
        $history = session()->get('past_searched_students', []);
        $regKey = $student->register_no ?: $student->application_no;

        // Remove if existing so it moves to position #1
        $history = array_values(array_filter($history, function ($item) use ($regKey, $student) {
            return ($item['reg_no'] ?? '') !== $regKey && ($item['application_no'] ?? '') !== $student->application_no;
        }));

        array_unshift($history, [
            'reg_no' => $regKey,
            'application_no' => $student->application_no,
            'name' => $student->student_name,
            'phone' => $student->mobile ?: '—',
            'email' => $student->personal_email ?: ($student->ofc_mail ?: '—'),
            'status' => self::normalizeStatus($student->std_status),
            'program' => $student->program?->program_name ?? ($student->program?->program_code ?? '—'),
            'searched_at' => now()->format('d M Y, h:i A'),
        ]);

        $history = array_slice($history, 0, 10);
        session()->put('past_searched_students', $history);
    }

    /**
     * Retrieve search history, pre-seeding with recent records if empty.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSearchHistory(): array
    {
        $history = session()->get('past_searched_students', []);

        if (empty($history)) {
            // Seed with latest students so the table is immediately populated with 10 records
            $seeded = StudentRegistration::with('program')
                ->whereNotNull('register_no')
                ->where('register_no', '!=', '')
                ->orderBy('application_no', 'desc')
                ->limit(10)
                ->get();

            foreach ($seeded as $s) {
                $history[] = [
                    'reg_no' => $s->register_no ?: $s->application_no,
                    'application_no' => $s->application_no,
                    'name' => $s->student_name,
                    'phone' => $s->mobile ?: '—',
                    'email' => $s->personal_email ?: ($s->ofc_mail ?: '—'),
                    'status' => self::normalizeStatus($s->std_status),
                    'program' => $s->program?->program_name ?? ($s->program?->program_code ?? '—'),
                    'searched_at' => $s->log_timestamp ? Carbon::parse($s->log_timestamp)->format('d M Y, h:i A') : 'Recent',
                ];
            }
            session()->put('past_searched_students', $history);
        }

        return $history;
    }

    /**
     * Update status in session history if present.
     */
    private function updateSearchHistoryStatus(string $regNo, string $newStatus): void
    {
        $history = session()->get('past_searched_students', []);
        foreach ($history as &$item) {
            if (($item['reg_no'] ?? '') === $regNo || ($item['application_no'] ?? '') === $regNo) {
                $item['status'] = $newStatus;
            }
        }
        unset($item);
        session()->put('past_searched_students', $history);
    }
}
