<?php

namespace Modules\Academics\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $type = $request->input('subtype');
        $mode = $request->input('submode');
        $q = trim((string) $request->input('q', ''));

        $subtypes = DB::table('academics.subjects')->whereNotNull('subtype')->distinct()->pluck('subtype');
        $submodes = DB::table('academics.subjects')->whereNotNull('submode')->distinct()->pluck('submode');

        $query = DB::table('academics.subjects');

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

        $totalRecords = DB::table('academics.subjects')->count();
        $filteredCount = (clone $query)->count();
        $subjects = $query->orderBy('subcode')->paginate(20);

        return view('academics::subjectlist', compact(
            'user',
            'menus',
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
        $this->requireLogin();

        $query = DB::table('academics.subjects')->orderBy('subcode');

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
