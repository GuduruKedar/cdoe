<?php

namespace Modules\Examinations\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Examinations\Models\marksMemo;

class ExamResultsController extends Controller
{
    use RequiresLogin;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('examinations::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('examinations::index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Student Marks with live search and multi-field filters
     */
    public function stdMarks(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null && ! ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest')) {
            return view('errors.no-role', compact('user'));
        }

        $q = trim((string) $request->input('q', ''));
        $sem = $request->input('sem');
        $type = $request->input('type');
        $division = $request->input('division');
        $examMonth = $request->input('month');
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $query = DB::table('examinations.marks_cgpa as m')
            ->leftJoin('admissions.student_registrations as s', 'm.regno', '=', 's.register_no')
            ->select(
                'm.mmno',
                'm.regno',
                's.student_name',
                'm.division_name',
                'm.mm_type',
                'm.exam_mon_yyyy',
                'm.s_year',
                'm.s_sem',
                'm.sgpa_perc',
                'm.cgpa_perc',
                'm.appno'
            )
            ->distinct();

        // Multi-token semantic search across reg no, student name, memo no, division, exam type, session
        if ($q !== '') {
            $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $token) {
                $query->where(function ($sub) use ($token) {
                    $term = "%{$token}%";
                    $sub->where('m.regno', 'ilike', $term)
                        ->orWhere('s.student_name', 'ilike', $term)
                        ->orWhere('m.mmno', 'ilike', $term)
                        ->orWhere('m.division_name', 'ilike', $term)
                        ->orWhere('m.mm_type', 'ilike', $term)
                        ->orWhere('m.exam_mon_yyyy', 'ilike', $term);
                });
            }
        }

        // Direct filters
        if (! empty($sem) && $sem !== 'all') {
            $query->where('m.s_sem', $sem);
        }
        if (! empty($type) && $type !== 'all') {
            $query->where('m.mm_type', $type);
        }
        if (! empty($division) && $division !== 'all') {
            $query->where('m.division_name', $division);
        }
        if (! empty($examMonth) && $examMonth !== 'all') {
            $query->where('m.exam_mon_yyyy', $examMonth);
        }

        $page = (int) $request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $totalCount = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->count();

        $items = $query->orderBy('m.mmno')
            ->orderBy('m.s_year')
            ->orderBy('m.s_sem')
            ->orderBy('m.exam_mon_yyyy')
            ->forPage($page, $perPage)
            ->get();

        $marksMemoList = new LengthAwarePaginator(
            $items,
            $totalCount,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $rowsHtml = view('examinations::partials.marks-rows', compact('marksMemoList'))->render();
            $paginationHtml = ($marksMemoList->count() > 0 && $marksMemoList->hasPages())
                ? $marksMemoList->links('pagination::bootstrap-5')->render()
                : '';

            return response()->json([
                'success' => true,
                'html' => $rowsHtml,
                'pagination' => $paginationHtml,
                'total' => $marksMemoList->total(),
                'count' => $marksMemoList->count(),
                'from' => $marksMemoList->firstItem() ?? 0,
                'to' => $marksMemoList->lastItem() ?? 0,
                'current_page' => $marksMemoList->currentPage(),
                'last_page' => $marksMemoList->lastPage(),
            ]);
        }

        $semesters = DB::table('examinations.marks_cgpa')->whereNotNull('s_sem')->distinct()->orderBy('s_sem')->pluck('s_sem');
        $types = DB::table('examinations.marks_cgpa')->whereNotNull('mm_type')->distinct()->orderBy('mm_type')->pluck('mm_type');
        $divisions = DB::table('examinations.marks_cgpa')->whereNotNull('division_name')->distinct()->orderBy('division_name')->pluck('division_name');
        $examMonths = DB::table('examinations.marks_cgpa')->whereNotNull('exam_mon_yyyy')->distinct()->orderBy('exam_mon_yyyy')->pluck('exam_mon_yyyy');
        $totalRecords = DB::table('examinations.marks_cgpa')->distinct()->count('mmno');

        return view('examinations::stdmarks', compact(
            'user',
            'menus',
            'marksMemoList',
            'q',
            'sem',
            'type',
            'division',
            'examMonth',
            'perPage',
            'semesters',
            'types',
            'divisions',
            'examMonths',
            'totalRecords'
        ));
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request, $id)
    {
        if ($id === 'stdmarks') {
            return $this->stdMarks($request);
        }

        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }
        $marksMemoList = marksMemo::select(['mmno', 'regno', 'division_name', 'mm_type', 'exam_mon_yyyy',
            's_year', 's_sem', 'sgpa_perc', 'cgpa_perc', 'appno'])
            ->distinct('mmno')
            ->orderBy('mmno')
            ->orderBy('s_year')
            ->orderBy('s_sem')
            ->orderBy('exam_mon_yyyy')
            ->get();

        return view('examinations::markslist', compact('marksMemoList', 'user', 'menus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('examinations::index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    public function marksMemo(string $memo)
    {
        $parts = explode('_', $memo);

        $mmno = $parts[0] ?? null;
        $regno = $parts[1] ?? null;
        $appno = ! empty($parts[2]) ? $parts[2] : null;

        if (empty($mmno) || empty($regno)) {
            abort(404, 'Invalid request.');
        }

        try {
            $query = marksMemo::byRegno($regno)
                ->where('mmno', $mmno);

            if (! empty($appno)) {
                $query->where('appno', $appno);
            }

            $valid = $query->firstOrFail();

        } catch (ModelNotFoundException $e) {
            abort(404, 'Invalid request.');
        }

        $student_info = StudentRegistration::select(['student_name', 'adm_batch_code', 'register_no', 'progid', 'electiveid'])->with(['program:program_id,program_long_name', 'electives:elective_id,elective_long_name'])->where('register_no', $regno)->first();
        $results = marksMemo::byMmno($mmno)->with('subject:subcode,subname,credits')->get();
        $basic_info = $valid->only([
            'mmno',
            's_sem',
            'exam_mon_yyyy',
            'results_mon_yyyy',
            'sgpa_perc',
            'division_name',

        ]);
        $totalMarks = $results->sum('total_earned');
        $examMarks = (int) $results->count() * 100;
        $formatter = new \NumberFormatter('en_IN', \NumberFormatter::SPELLOUT);
        $totalMarks = ucfirst($formatter->format($totalMarks));
        $maxMarks = ucfirst($formatter->format($examMarks));

        return view('examinations::marksmemo', compact(
            'basic_info',
            'results', 'student_info', 'totalMarks', 'examMarks', 'maxMarks', 'totalMarks'
        ));
    }

    public function marksMemoList(Request $request)
    {
        $reg_no = Session::get('user.usercode');
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }
        $marksMemoList = marksMemo::byRegno($reg_no)
            ->select(['mmno', 'regno', 'division_name', 'mm_type', 'exam_mon_yyyy',
                's_year', 's_sem', 'sgpa_perc', 'cgpa_perc', 'appno'])
            ->distinct('mmno')
            ->orderBy('mmno')
            ->orderBy('s_year')
            ->orderBy('s_sem')
            ->orderBy('exam_mon_yyyy')
            ->get();

        return view('examinations::markslist', compact('marksMemoList', 'user', 'menus'));

    }
}
