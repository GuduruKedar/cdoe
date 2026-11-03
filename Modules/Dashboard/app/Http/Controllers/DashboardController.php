<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admissions\Models\Programs;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Examinations\Models\MarksMemo;
use Modules\Hrms\Models\Employee;
use Modules\Visa\Models\TicketDetail;

class DashboardController extends Controller
{
    use RequiresLogin;

    /**
     * Display the role-appropriate dashboard (Student vs Faculty/Staff).
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);

        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $userCode = $user->usercode ?? session('user.usercode');
        $activeRoleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));
        $isStudent = ($activeRoleId === 5) || ((int) $user->user_type === 1);

        $userName = Employee::where('emp_code', $userCode)->value('full_name')
            ?: StudentRegistration::where('register_no', $userCode)->value('student_name')
            ?: ($user->usercode ?? 'User');

        // ================= 1. STUDENT DASHBOARD =================
        if ($isStudent) {
            $student = StudentRegistration::with('program')
                ->where('register_no', $userCode)
                ->first();

            $isPreview = false;
            if (! $student) {
                // If employee or admin switched to the student role for preview/testing
                $student = StudentRegistration::with('program')->first();
                $isPreview = true;
            }

            $regNo = $student?->register_no;
            $studentDisplayName = $student?->student_name ?: $userName;

            // Student's personal marks and curriculum progress
            $marks = $regNo
                ? MarksMemo::where('regno', $regNo)->orderBy('s_sem')->orderBy('subcode')->get()
                : collect();

            $totalSubjects = $marks->count();
            $passedCount = $marks->whereNotIn('letter_grade', ['F', 'NA', ''])->count();
            $backlogsCount = $marks->where('letter_grade', 'F')->count();

            // Latest SGPA & CGPA
            $latestSgpa = $marks->whereNotNull('sgpa_perc')->whereNotIn('sgpa_perc', ['NA', '', '0'])->last()?->sgpa_perc ?? '—';
            $latestCgpa = $marks->whereNotNull('cgpa_perc')->whereNotIn('cgpa_perc', ['NA', '', '0'])->last()?->cgpa_perc ?? '—';
            $currentSem = $marks->max('s_sem') ?: ($student?->adm_sem ?: 1);

            // Student's support tickets
            $myTicketsCount = $regNo ? TicketDetail::where('regno', $regNo)->count() : 0;
            $openTicketsCount = $regNo ? TicketDetail::where('regno', $regNo)->whereIn('std_tkt_status', [
                TicketDetail::STATUS_RAISED,
                TicketDetail::STATUS_PENDING,
                TicketDetail::STATUS_IN_PROCESS,
            ])->count() : 0;

            $studentStats = [
                'program' => $student?->program?->program_short_name ?: ($student?->program?->program_code ?: 'Online Degree'),
                'program_full' => $student?->program?->program_long_name ?: 'Online Degree Curriculum',
                'batch' => $student?->adm_batch_code ? str_replace('_', ' ', $student->adm_batch_code) : 'Active Batch',
                'current_sem' => $currentSem,
                'status' => $student?->std_status ?: 'Active',
                'total_subjects' => $totalSubjects,
                'passed' => $passedCount,
                'backlogs' => $backlogsCount,
                'latest_sgpa' => $latestSgpa,
                'latest_cgpa' => $latestCgpa,
                'tickets_count' => $myTicketsCount,
                'open_tickets' => $openTicketsCount,
            ];

            return view('welcome.student', compact(
                'user',
                'menus',
                'student',
                'studentDisplayName',
                'studentStats',
                'marks',
                'isPreview'
            ));
        }

        // ================= 2. FACULTY & STAFF DASHBOARD =================
        $stats = [
            'students' => StudentRegistration::count(),
            'employees' => Employee::count(),
            'marks' => MarksMemo::distinct()->count('mmno'),
            'programs' => Programs::count(),
        ];

        // Recent student registrations preview
        $recentStudents = StudentRegistration::with('program:program_id,program_short_name')
            ->orderBy('register_no', 'desc')
            ->limit(5)
            ->get(['register_no', 'student_name', 'progid', 'std_status']);

        // Dashboard chart datasets: optimal Eloquent relationship aggregation
        $programDistribution = Programs::select('program_short_name as name')
            ->withCount('students as total')
            ->has('students')
            ->get();

        $gradeDistribution = MarksMemo::selectRaw('letter_grade, count(*) as total')
            ->whereNotNull('letter_grade')
            ->groupBy('letter_grade')
            ->orderByDesc('total')
            ->get();

        $genderDistribution = StudentRegistration::selectRaw("COALESCE(gender, 'Unspecified') as gender, count(*) as total")
            ->groupBy('gender')
            ->get();

        return view('welcome.faculty', compact(
            'user',
            'menus',
            'stats',
            'userName',
            'recentStudents',
            'programDistribution',
            'gradeDistribution',
            'genderDistribution'
        ));
    }
}
