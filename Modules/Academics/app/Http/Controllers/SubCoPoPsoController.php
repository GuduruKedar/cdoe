<?php

namespace Modules\Academics\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academics\Models\CoPoPsoMapping;
use Modules\Academics\Models\Subject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubCoPoPsoController extends Controller
{
    use RequiresLogin;

    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $selectedSubject = $request->input('subcode');
        $selectedCo = $request->input('coname');
        $q = trim((string) $request->input('q', ''));

        $mappingTable = (new CoPoPsoMapping)->getTable();
        $subjectTable = (new Subject)->getTable();

        // Distinct subjects for dropdown
        $subjectsList = CoPoPsoMapping::from("{$mappingTable} as m")
            ->leftJoin("{$subjectTable} as s", 'm.subcode', '=', 's.subcode')
            ->select('m.subcode', 's.subname')
            ->distinct()
            ->orderBy('m.subcode')
            ->get();

        // Distinct CO names for dropdown
        $coList = CoPoPsoMapping::whereNotNull('coname')
            ->distinct()
            ->orderBy('coname')
            ->pluck('coname');

        $query = CoPoPsoMapping::from("{$mappingTable} as m")
            ->leftJoin("{$subjectTable} as s", 'm.subcode', '=', 's.subcode')
            ->select(
                'm.*',
                's.subname',
                's.subtype',
                's.submode'
            );

        if ($selectedSubject && $selectedSubject !== 'all') {
            $query->where('m.subcode', $selectedSubject);
        }

        if ($selectedCo && $selectedCo !== 'all') {
            $query->where('m.coname', $selectedCo);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('m.subcode', 'ILIKE', "%{$q}%")
                    ->orWhere('s.subname', 'ILIKE', "%{$q}%")
                    ->orWhere('m.coname', 'ILIKE', "%{$q}%")
                    ->orWhere('m.copopsoid', 'ILIKE', "%{$q}%");
            });
        }

        $totalRecords = CoPoPsoMapping::count();
        $filteredCount = (clone $query)->count();
        $mappings = $query->orderBy('m.subcode')->orderBy('m.coname')->paginate(20);

        return view('academics::subcopopso', compact(
            'user',
            'menus',
            'mappings',
            'subjectsList',
            'coList',
            'selectedSubject',
            'selectedCo',
            'q',
            'totalRecords',
            'filteredCount'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->requireLogin();

        $selectedSubject = $request->input('subcode');
        $selectedCo = $request->input('coname');
        $q = trim((string) $request->input('q', ''));

        $mappingTable = (new CoPoPsoMapping)->getTable();
        $subjectTable = (new Subject)->getTable();

        $query = CoPoPsoMapping::from("{$mappingTable} as m")
            ->leftJoin("{$subjectTable} as s", 'm.subcode', '=', 's.subcode')
            ->select('m.*', 's.subname');

        if ($selectedSubject && $selectedSubject !== 'all') {
            $query->where('m.subcode', $selectedSubject);
        }

        if ($selectedCo && $selectedCo !== 'all') {
            $query->where('m.coname', $selectedCo);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('m.subcode', 'ILIKE', "%{$q}%")
                    ->orWhere('s.subname', 'ILIKE', "%{$q}%")
                    ->orWhere('m.coname', 'ILIKE', "%{$q}%")
                    ->orWhere('m.copopsoid', 'ILIKE', "%{$q}%");
            });
        }

        $query->orderBy('m.subcode')->orderBy('m.coname');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sub_co_po_pso_mapping_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'S.No',
                'Subject Code',
                'Subject Name',
                'Mapping ID',
                'Course Outcome (CO)',
                'PO1', 'PO2', 'PO3', 'PO4', 'PO5', 'PO6', 'PO7', 'PO8', 'PO9', 'PO10',
                'PSO1', 'PSO2', 'PSO3', 'PSO4', 'PSO5',
            ]);

            $i = 1;
            foreach ($query->get() as $row) {
                fputcsv($handle, [
                    $i++,
                    $row->subcode,
                    $row->subname ?? '—',
                    $row->copopsoid,
                    $row->coname,
                    $row->po1,
                    $row->po2,
                    $row->po3,
                    $row->po4,
                    $row->po5,
                    $row->po6,
                    $row->po7,
                    $row->po8,
                    $row->po9,
                    $row->po10,
                    $row->pso1,
                    $row->pso2,
                    $row->pso3,
                    $row->pso4,
                    $row->pso5,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
