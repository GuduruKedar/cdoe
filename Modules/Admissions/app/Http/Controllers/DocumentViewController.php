<?php

namespace Modules\Admissions\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentViewController extends Controller
{
    use RequiresLogin;

    /**
     * Display the Document Verification / View matrix table.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $program = $request->input('program');
        $batch = $request->input('batch');
        $status = $request->input('status');
        $q = trim((string) $request->input('q', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        // Distinct filter options
        $programs = DB::table('master.programs')
            ->orderBy('program_code')
            ->select('program_id', 'program_code', 'program_long_name')
            ->get();

        $batches = DB::table('admissions.student_registrations')
            ->whereNotNull('adm_batch_code')
            ->distinct()
            ->orderBy('adm_batch_code')
            ->pluck('adm_batch_code');

        $statuses = DB::table('admissions.student_registrations')
            ->whereNotNull('std_status')
            ->distinct()
            ->orderBy('std_status')
            ->pluck('std_status');

        $totalRecords = DB::table('admissions.student_registrations')->count();

        // Build main student query
        $query = DB::table('admissions.student_registrations as sr')
            ->leftJoin('master.programs as p', 'sr.progid', '=', 'p.program_id')
            ->select(
                'sr.application_no',
                'sr.register_no',
                'sr.student_name',
                'sr.adm_batch_code',
                'sr.gender',
                'sr.std_status',
                'p.program_code',
                'p.program_long_name'
            );

        if ($program && $program !== 'all') {
            $query->where('sr.progid', (int) $program);
        }
        if ($batch && $batch !== 'all') {
            $query->where('sr.adm_batch_code', $batch);
        }
        if ($status && $status !== 'all') {
            $query->where('sr.std_status', $status);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('sr.register_no', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.application_no', is_numeric($q) ? (int) $q : -1)
                    ->orWhere('sr.personal_email', 'ILIKE', "%{$q}%");
            });
        }

        $filteredCount = (clone $query)->count();
        $students = $query->orderBy('sr.application_no', 'asc')->paginate($perPage);

        // Fetch documents for the current page of students
        $appNos = $students->pluck('application_no')->toArray();
        $docsRaw = DB::table('admissions.student_documents')
            ->whereIn('application_no', $appNos)
            ->get();

        // Group documents by application_no and normalized doctype
        $studentDocs = [];
        foreach ($docsRaw as $d) {
            $type = strtolower(trim($d->doctype ?: $d->docname ?: 'other'));
            // Map variations
            if (in_array($type, ['photo', 'passport_photo', 'avatar'])) {
                $normType = 'photo';
            } elseif (in_array($type, ['10th', 'ssc', 'tenth'])) {
                $normType = 'ssc';
            } elseif (in_array($type, ['inter', '12th', 'plus2', 'intermediate'])) {
                $normType = 'inter';
            } elseif (in_array($type, ['ug', 'degree', 'btech', 'ug_degree', 'ug1'])) {
                $normType = 'ug';
            } elseif (in_array($type, ['pg', 'pg_degree', 'mtech', 'mba', 'mca', 'pg1'])) {
                $normType = 'pg';
            } elseif (in_array($type, ['aadhar', 'aadhaar'])) {
                $normType = 'aadhar';
            } elseif ($type === 'pan') {
                $normType = 'pan';
            } elseif ($type === 'apaar') {
                $normType = 'apaar';
            } elseif (in_array($type, ['diploma'])) {
                $normType = 'diploma';
            } elseif (in_array($type, ['phd', 'doctorate'])) {
                $normType = 'phd';
            } else {
                $normType = 'other_id';
            }

            $d->file_url = $this->resolveFileUrl($d->docfile);
            $d->is_image = in_array(strtolower($d->doctype), ['photo', 'avatar', 'image']);
            $studentDocs[$d->application_no][$normType] = $d;
            // Also store first docno found
            if (! isset($studentDocs[$d->application_no]['_docno'])) {
                $studentDocs[$d->application_no]['_docno'] = $d->docno ?: 'DOC-'.$d->doc_id;
            }
        }

        return view('admissions::docview', compact(
            'user',
            'menus',
            'students',
            'studentDocs',
            'programs',
            'batches',
            'statuses',
            'program',
            'batch',
            'status',
            'q',
            'totalRecords',
            'filteredCount'
        ));
    }

    /**
     * Download or view a single document.
     */
    public function downloadDocument(int $docId)
    {
        $this->requireLogin();

        $doc = DB::table('admissions.student_documents')->where('doc_id', $docId)->first();
        if (! $doc || empty($doc->docfile)) {
            abort(404, 'Document record not found');
        }

        if (Storage::disk('public')->exists($doc->docfile)) {
            return Storage::disk('public')->download($doc->docfile, $doc->docname ?: basename($doc->docfile));
        }

        // Fallback if stored as relative path
        $path = public_path('storage/'.ltrim($doc->docfile, '/'));
        if (file_exists($path)) {
            return response()->download($path, $doc->docname ?: basename($path));
        }

        abort(404, 'File not found on disk');
    }

    /**
     * Export document view matrix to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->requireLogin();

        $program = $request->input('program');
        $batch = $request->input('batch');
        $status = $request->input('status');
        $q = trim((string) $request->input('q', ''));

        $query = DB::table('admissions.student_registrations as sr')
            ->leftJoin('master.programs as p', 'sr.progid', '=', 'p.program_id')
            ->select('sr.application_no', 'sr.register_no', 'sr.student_name', 'sr.adm_batch_code', 'sr.std_status', 'p.program_code');

        if ($program && $program !== 'all') {
            $query->where('sr.progid', (int) $program);
        }
        if ($batch && $batch !== 'all') {
            $query->where('sr.adm_batch_code', $batch);
        }
        if ($status && $status !== 'all') {
            $query->where('sr.std_status', $status);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('sr.register_no', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%");
            });
        }

        $records = $query->orderBy('sr.application_no', 'asc')->get();
        $appNos = $records->pluck('application_no')->toArray();

        $docs = DB::table('admissions.student_documents')
            ->whereIn('application_no', $appNos)
            ->get()
            ->groupBy('application_no');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_documents_matrix_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($records, $docs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'S.No', 'Application No', 'Register No', 'Student Name', 'Program', 'Batch',
                'Photo', 'Aadhar', 'PAN', 'APAAR', 'Other ID', 'SSC (10th)', 'Inter (+2)', 'Diploma', 'UG', 'PG', 'PhD',
            ]);

            $docTypesMap = ['photo', 'aadhar', 'pan', 'apaar', 'other', '10th', 'inter', 'diploma', 'ug', 'pg', 'phd'];
            $i = 1;

            foreach ($records as $row) {
                $rowDocs = $docs->get($row->application_no, collect())->keyBy(function ($d) {
                    return strtolower($d->doctype ?: 'other');
                });

                $line = [
                    $i++,
                    $row->application_no,
                    $row->register_no,
                    $row->student_name,
                    $row->program_code,
                    $row->adm_batch_code,
                ];

                foreach ($docTypesMap as $t) {
                    $line[] = $rowDocs->has($t) ? 'Uploaded' : 'NA';
                }

                fputcsv($handle, $line);
            }

            fclose($handle);
        }, 200, $headers);
    }

    protected function resolveFileUrl(?string $path): string
    {
        if (empty($path)) {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
