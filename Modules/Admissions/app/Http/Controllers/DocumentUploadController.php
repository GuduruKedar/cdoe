<?php

namespace Modules\Admissions\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Admissions\Models\Programs;
use Modules\Admissions\Models\StudentDocument;
use Modules\Admissions\Models\StudentRegistration;

class DocumentUploadController extends Controller
{
    use RequiresLogin;

    /**
     * Display the document upload portal interface.
     */
    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $userType = (int) ($user->user_type ?? session('user.user_type', 0));
        $isStudent = ($userType === 1);

        $docTypes = [
            'photo' => 'Student Photograph (Passport Size)',
            'ssc' => 'SSC / 10th Class Marks Memo',
            'inter' => 'Intermediate / +2 / Diploma Certificate',
            'ug' => 'Undergraduate (UG) Degree Certificate & Consolidated Memo',
            'pg' => 'Postgraduate (PG) Certificate (if applicable)',
            'aadhar' => 'Aadhar Card (Identity Proof)',
            'pan' => 'PAN Card',
            'apaar' => 'APAAR / ABC ID Card',
            'other' => 'Other Academic / Official Document',
        ];

        $defaultRegNo = $request->input('reg_no');
        if ($isStudent && empty($defaultRegNo)) {
            $defaultRegNo = $user->usercode ?? session('user.usercode');
        }

        $srTable = (new StudentRegistration)->getTable();
        $pTable = (new Programs)->getTable();

        // Fetch top recent students for quick one-click selection via Eloquent
        $recentStudents = StudentRegistration::from("{$srTable} as sr")
            ->leftJoin("{$pTable} as p", 'sr.progid', '=', 'p.program_id')
            ->orderBy('sr.application_no', 'asc')
            ->limit(6)
            ->select(
                'sr.application_no',
                'sr.register_no',
                'sr.student_name',
                'sr.personal_email',
                'sr.mobile',
                'sr.gender',
                'sr.adm_batch_code',
                'p.program_code',
                'p.program_long_name'
            )
            ->get();

        if (empty($defaultRegNo) && ! $isStudent && $recentStudents->isNotEmpty()) {
            $defaultRegNo = $recentStudents->first()->register_no;
        }

        $selectedStudent = null;
        $uploadedDocuments = collect();

        if (! empty($defaultRegNo)) {
            $selectedStudent = StudentRegistration::from("{$srTable} as sr")
                ->leftJoin("{$pTable} as p", 'sr.progid', '=', 'p.program_id')
                ->where('sr.register_no', $defaultRegNo)
                ->orWhere('sr.application_no', is_numeric($defaultRegNo) ? (int) $defaultRegNo : -1)
                ->select(
                    'sr.application_no',
                    'sr.register_no',
                    'sr.student_name',
                    'sr.personal_email',
                    'sr.mobile',
                    'sr.gender',
                    'sr.adm_batch_code',
                    'p.program_code',
                    'p.program_long_name'
                )
                ->first();

            if ($selectedStudent) {
                $uploadedDocuments = StudentDocument::where('application_no', (string) $selectedStudent->application_no)
                    ->orderBy('doc_id', 'desc')
                    ->get()
                    ->map(function ($d) {
                        $d->file_url = $this->resolveFileUrl($d->docfile);
                        $d->is_image = in_array(strtolower((string) $d->doctype), ['photo', 'avatar', 'image'], true);

                        return $d;
                    });
            }
        }

        return view('admissions::docupload', compact(
            'user',
            'menus',
            'docTypes',
            'isStudent',
            'selectedStudent',
            'recentStudents',
            'uploadedDocuments'
        ));
    }

    /**
     * AJAX search/lookup for students by register_no, application_no, or name.
     */
    public function lookupStudent(Request $request): JsonResponse
    {
        $this->requireLogin();
        $q = trim((string) $request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['students' => []]);
        }

        $srTable = (new StudentRegistration)->getTable();
        $pTable = (new Programs)->getTable();

        $students = StudentRegistration::from("{$srTable} as sr")
            ->leftJoin("{$pTable} as p", 'sr.progid', '=', 'p.program_id')
            ->where(function ($w) use ($q) {
                $w->where('sr.register_no', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.student_name', 'ILIKE', "%{$q}%")
                    ->orWhere('sr.application_no', is_numeric($q) ? (int) $q : -1)
                    ->orWhere('sr.personal_email', 'ILIKE', "%{$q}%");
            })
            ->limit(15)
            ->select(
                'sr.application_no',
                'sr.register_no',
                'sr.student_name',
                'sr.personal_email',
                'sr.mobile',
                'sr.gender',
                'sr.adm_batch_code',
                'p.program_code',
                'p.program_long_name'
            )
            ->get();

        return response()->json(['students' => $students]);
    }

    /**
     * AJAX fetch existing documents for a student.
     */
    public function getStudentDocuments(Request $request): JsonResponse
    {
        $this->requireLogin();
        $appNo = (int) $request->input('application_no');
        $regNo = trim((string) $request->input('register_no', ''));

        $query = StudentDocument::query();
        if ($appNo > 0) {
            $query->where('application_no', (string) $appNo);
        } elseif (! empty($regNo)) {
            $query->where('register_no', $regNo);
        } else {
            return response()->json(['documents' => []]);
        }

        $docs = $query->orderBy('doc_id', 'desc')
            ->get()
            ->map(function ($d) {
                $d->file_url = $this->resolveFileUrl($d->docfile);
                $d->is_image = in_array(strtolower((string) $d->doctype), ['photo', 'avatar', 'image'], true);

                return $d;
            });

        return response()->json(['documents' => $docs]);
    }

    /**
     * Handle document upload (single or multiple line-by-line).
     */
    public function upload(Request $request): JsonResponse
    {
        $user = $this->requireLogin();

        $appNo = (int) $request->input('application_no');
        $regNo = trim((string) $request->input('register_no', ''));
        $docType = strtolower(trim((string) $request->input('doctype', '')));

        if (! $appNo || empty($docType) || ! $request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing student application number, document type, or file.',
            ], 422);
        }

        // Validate student exists via Eloquent
        $student = StudentRegistration::where('application_no', (string) $appNo)->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => 'Student registration record not found.',
            ], 404);
        }

        $regNo = $student->register_no ?? $regNo;
        $file = $request->file('file');

        // Requirement rule: Photo must be jpg/jpeg/png, all other documents must be PDF
        if ($docType === 'photo') {
            $ext = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo must be in JPG/JPEG/PNG format.',
                ], 422);
            }
        } else {
            $ext = strtolower($file->getClientOriginalExtension());
            if ($ext !== 'pdf') {
                return response()->json([
                    'success' => false,
                    'message' => "Document '{$docType}' must be in PDF format.",
                ], 422);
            }
        }

        // Generate safe unique filename
        $safeReg = preg_replace('/[^a-zA-Z0-9_-]/', '_', $regNo ?: (string) $appNo);
        $cleanDocType = preg_replace('/[^a-zA-Z0-9_-]/', '_', $docType);
        $filename = "doc_{$safeReg}_{$cleanDocType}_".time().'_'.Str::random(6).'.'.$ext;

        // Store into public/documents
        $storedPath = $file->storeAs('documents', $filename, 'public');
        $docNo = 'DOC-'.strtoupper(Str::random(8));

        // Insert into database via StudentDocument model
        $userCode = session('user.usercode', $user->usercode ?? 'SYSTEM');
        $ip = $request->ip();

        $doc = StudentDocument::create([
            'application_no' => (string) $appNo,
            'register_no' => $regNo,
            'docno' => $docNo,
            'docname' => $file->getClientOriginalName(),
            'doctype' => $docType,
            'docfile' => $storedPath,
            'lock_status' => 0,
            'log_userid' => $userCode,
            'log_ipaddress' => $ip,
            'log_timestamp' => now(),
        ]);

        $insertedId = $doc->doc_id;

        ActivityLogger::log('ADMISSION', 'DOC_UPLOAD', "Uploaded {$docType} for student {$regNo} (App: {$appNo})", [
            'application_no' => $appNo,
            'register_no' => $regNo,
            'doctype' => $docType,
            'doc_id' => $insertedId,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Document '{$docType}' uploaded successfully.",
            'document' => [
                'doc_id' => $insertedId,
                'application_no' => $appNo,
                'register_no' => $regNo,
                'docno' => $docNo,
                'docname' => $file->getClientOriginalName(),
                'doctype' => $docType,
                'docfile' => $storedPath,
                'file_url' => asset('storage/'.$storedPath),
                'is_image' => in_array($docType, ['photo', 'avatar', 'image'], true),
                'log_timestamp' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Delete an uploaded document.
     */
    public function destroy(Request $request, int $docId): JsonResponse
    {
        $this->requireLogin();

        $doc = StudentDocument::find($docId);
        if (! $doc) {
            return response()->json(['success' => false, 'message' => 'Document not found.'], 404);
        }

        // Delete physical file if exists
        if (! empty($doc->docfile) && Storage::disk('public')->exists($doc->docfile)) {
            Storage::disk('public')->delete($doc->docfile);
        }

        $doc->delete();

        ActivityLogger::log('ADMISSION', 'DOC_DELETE', "Deleted document #{$docId} ({$doc->doctype}) for student {$doc->register_no}", [
            'doc_id' => $docId,
            'register_no' => $doc->register_no,
        ]);

        return response()->json(['success' => true, 'message' => 'Document deleted successfully.']);
    }

    /**
     * Resolve public URL for a stored docfile.
     */
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
