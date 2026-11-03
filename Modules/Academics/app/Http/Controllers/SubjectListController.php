<?php

namespace Modules\Academics\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Academics\Models\CourseRegistration;
use Modules\Academics\Models\Subject;
use Modules\Admissions\Models\StudentRegistration;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubjectListController extends Controller
{
    use RequiresLogin;

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

        $q = trim((string) $request->input('q', ''));
        $sem = $request->input('sem', 'all');

        // ================= 1. STUDENT VIEW: Respective Registered Subjects from Course Registration =================
        if ($isStudent) {
            $student = StudentRegistration::with('program')->where('register_no', $userCode)->first();
            $isPreview = false;
            $regNo = $student?->register_no;

            // If user has no personal course registrations or admin is previewing student role
            if (! $regNo || ! CourseRegistration::where('regno', $regNo)->exists()) {
                $demoRegNo = CourseRegistration::whereNotNull('regno')->value('regno') ?: '261CK01026';
                $student = StudentRegistration::with('program')->where('register_no', $demoRegNo)->first()
                    ?: (object) [
                        'register_no' => $demoRegNo,
                        'student_name' => 'Demo Student',
                        'adm_batch_code' => 'JANUARY_2026',
                        'program' => (object) ['program_short_name' => 'BBA', 'program_long_name' => 'Bachelor of Business Administration'],
                    ];
                $isPreview = true;
                $regNo = $demoRegNo;
            }

            $query = CourseRegistration::with('subject')
                ->where('regno', $regNo);

            if ($sem && $sem !== 'all') {
                $query->where('s_sem', (int) $sem);
            }

            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('subcode', 'ILIKE', "%{$q}%")
                        ->orWhereHas('subject', function ($sq) use ($q) {
                            $sq->where('subname', 'ILIKE', "%{$q}%");
                        });
                });
            }

            $availableSemesters = CourseRegistration::where('regno', $regNo)
                ->whereNotNull('s_sem')
                ->distinct()
                ->orderBy('s_sem')
                ->pluck('s_sem');

            $totalRecords = CourseRegistration::where('regno', $regNo)->count();
            $filteredCount = (clone $query)->count();
            $subjects = $query->orderBy('s_sem')->orderBy('subcode')->paginate(20);

            return view('academics::subjectlist', compact(
                'user',
                'menus',
                'isStudent',
                'student',
                'isPreview',
                'subjects',
                'availableSemesters',
                'sem',
                'q',
                'totalRecords',
                'filteredCount'
            ));
        }

        // ================= 2. FACULTY & STAFF VIEW: Complete Curriculum Catalog =================
        $type = $request->input('subtype');
        $mode = $request->input('submode');

        $subtypes = Subject::whereNotNull('subtype')->distinct()->pluck('subtype');
        $submodes = Subject::whereNotNull('submode')->distinct()->pluck('submode');

        $query = Subject::query();

        if ($type && $type !== 'all') {
            $query->where('subtype', $type);
        }
        if ($mode && $mode !== 'all') {
            $query->where('submode', $mode);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('subcode', 'ILIKE', "%{$q}%")
                    ->orWhere('subname', 'ILIKE', "%{$q}%");
            });
        }

        $totalRecords = Subject::count();
        $filteredCount = (clone $query)->count();
        $subjects = $query->orderBy('subcode')->paginate(20);

        return view('academics::subjectlist', compact(
            'user',
            'menus',
            'isStudent',
            'subjects',
            'subtypes',
            'submodes',
            'type',
            'mode',
            'q',
            'totalRecords',
            'filteredCount'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $this->requireLogin();
        $userCode = $user->usercode ?? session('user.usercode');
        $activeRoleId = (int) (session('user.role_id') ?: ($user->role_id ?? 0));
        $isStudent = ($activeRoleId === 5) || ((int) $user->user_type === 1);

        if ($isStudent) {
            $student = StudentRegistration::with('program')->where('register_no', $userCode)->first();
            $regNo = $student?->register_no;
            if (! $regNo || ! CourseRegistration::where('regno', $regNo)->exists()) {
                $regNo = CourseRegistration::whereNotNull('regno')->value('regno') ?: '261CK01026';
            }

            $query = CourseRegistration::with('subject')
                ->where('regno', $regNo)
                ->orderBy('s_sem')
                ->orderBy('subcode');

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="student_subjects_'.$regNo.'_'.date('Ymd_His').'.csv"',
            ];

            return response()->stream(function () use ($query) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, ['S.No', 'Register No', 'Semester', 'Year', 'Subject Code', 'Subject Name', 'Type', 'Credits', 'Reg Type', 'Reg Date']);

                $i = 1;
                foreach ($query->get() as $cr) {
                    fputcsv($handle, [
                        $i++,
                        $cr->regno,
                        $cr->s_sem ? 'Semester '.$cr->s_sem : '',
                        $cr->s_year ? 'Year '.$cr->s_year : '',
                        $cr->subcode,
                        $cr->subject?->subname ?? '',
                        $cr->subject?->subtype ?? 'THEORY',
                        $cr->subject?->credits ?? 4,
                        $cr->reg_type ?? 'REGULAR',
                        $cr->reg_date ? Carbon::parse($cr->reg_date)->format('d-m-Y') : '',
                    ]);
                }
                fclose($handle);
            }, 200, $headers);
        }

        $query = Subject::orderBy('subcode');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="subjects_curriculum_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['S.No', 'Subject Code', 'Subject Name', 'Type', 'L', 'T', 'P', 'C', 'Credits', 'Mode', 'Effective Date']);

            $i = 1;
            foreach ($query->get() as $s) {
                fputcsv($handle, [
                    $i++,
                    $s->subcode,
                    $s->subname,
                    $s->subtype,
                    $s->l,
                    $s->t,
                    $s->p,
                    $s->c,
                    $s->credits,
                    $s->submode,
                    $s->subdate,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
