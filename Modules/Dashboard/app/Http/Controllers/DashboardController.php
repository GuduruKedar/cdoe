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

class DashboardController extends Controller
{
    use RequiresLogin;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);

        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $userCode = $user->usercode ?? session('user.usercode');
        $userName = Employee::where('emp_code', $userCode)->value('full_name')
            ?: StudentRegistration::where('register_no', $userCode)->value('student_name')
            ?: ($user->usercode ?? 'Administrator');

        // Dashboard statistics via Eloquent models
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
            ->orderByDesc('total')
            ->get();

        $gradeDistribution = MarksMemo::selectRaw('letter_grade, count(*) as total')
            ->whereNotNull('letter_grade')
            ->groupBy('letter_grade')
            ->orderByDesc('total')
            ->get();

        $genderDistribution = StudentRegistration::selectRaw("COALESCE(gender, 'Unspecified') as gender, count(*) as total")
            ->groupBy('gender')
            ->get();

        // Route based on user_type
        if ((int) $user->user_type === 1) {
            return view('welcome.student', compact('user', 'menus', 'stats', 'userName', 'recentStudents'));
        }
        if ((int) $user->user_type === 2) {
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

        return view('errors.no-role', compact('user'));
    }
}
