<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use RequiresLogin;

    public function index()
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);

        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $userCode = $user->usercode ?? session('user.usercode');
        $userName = DB::table('hrms.employees')->where('emp_code', $userCode)->value('full_name')
            ?: DB::table('admissions.student_registrations')->where('register_no', $userCode)->value('student_name')
            ?: ($user->usercode ?? 'Administrator');

        // Dashboard statistics
        $stats = [
            'students' => DB::table('admissions.student_registrations')->count(),
            'employees' => DB::table('hrms.employees')->count(),
            'marks' => DB::table('examinations.marks_cgpa')->distinct()->count('mmno'),
            'programs' => DB::table('master.programs')->count(),
        ];

        // Recent student registrations preview
        $recentStudents = DB::table('admissions.student_registrations as sr')
            ->leftJoin('master.programs as p', 'sr.progid', '=', 'p.program_id')
            ->select('sr.register_no', 'sr.student_name', 'p.program_short_name', 'sr.std_status')
            ->orderBy('sr.register_no', 'desc')
            ->limit(5)
            ->get();

        // Dashboard chart datasets
        $programDistribution = DB::table('admissions.student_registrations as sr')
            ->leftJoin('master.programs as p', 'sr.progid', '=', 'p.program_id')
            ->selectRaw("COALESCE(p.program_short_name, 'Other') as name, count(*) as total")
            ->groupBy('p.program_short_name')
            ->orderByDesc('total')
            ->get();

        $gradeDistribution = DB::table('examinations.marks_cgpa')
            ->selectRaw('letter_grade, count(*) as total')
            ->whereNotNull('letter_grade')
            ->groupBy('letter_grade')
            ->orderByDesc('total')
            ->get();

        $genderDistribution = DB::table('admissions.student_registrations')
            ->selectRaw("COALESCE(gender, 'Unspecified') as gender, count(*) as total")
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
