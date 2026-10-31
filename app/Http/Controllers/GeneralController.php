<?php

namespace App\Http\Controllers;

use App\Common\Services\MenuService;
use App\Models\PprDetail;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeneralController extends Controller
{
    /**
     * Program definition mapping (Prog ID to Code/Name).
     */
    public const PROGRAM_MAP = [
        'MBA' => ['id' => 1, 'name' => 'MBA', 'title' => 'Online MBA'],
        'MCA' => ['id' => 2, 'name' => 'MCA', 'title' => 'Online MCA'],
        'BBA' => ['id' => 3, 'name' => 'BBA', 'title' => 'Online BBA'],
        'BCA' => ['id' => 4, 'name' => 'BCA', 'title' => 'Online BCA'],
    ];

    /**
     * Check if the current ACTIVE role is Main Admin.
     */
    public static function isMainAdmin(): bool
    {
        $roleId = (int) session('user.role_id');

        // Role 2 is Main Admin(BHANU)
        if ($roleId === 2) {
            return true;
        }

        if ($roleId) {
            $roleName = Role::where('role_id', $roleId)->value('role_name');
            if ($roleName && str_contains(strtolower($roleName), 'main admin')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Main General Regulations Hub (Cards View).
     * Accessible by ALL roles.
     */
    public function index(Request $request)
    {
        $user = (object) [
            'usercode' => session('user.usercode'),
            'role_id' => session('user.role_id'),
        ];
        $menus = MenuService::getMenusForUser($user);

        $activeProgram = strtoupper(trim($request->query('program', 'ALL')));
        $search = trim($request->query('q', ''));

        $query = PprDetail::query();

        // Filter by program (MCA / BBA / BCA / MBA)
        if ($activeProgram && $activeProgram !== 'ALL') {
            $query->where('prog_name', $activeProgram);
        }

        // Search in title, code, description, filename
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('regname', 'ILIKE', "%{$search}%")
                    ->orWhere('regcode', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%")
                    ->orWhere('filename', 'ILIKE', "%{$search}%");
            });
        }

        $documents = $query->orderBy('prog_name')
            ->orderBy('regcode', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate dynamic program counts
        $counts = [
            'ALL' => PprDetail::count(),
            'MCA' => PprDetail::where('prog_name', 'MCA')->count(),
            'MBA' => PprDetail::where('prog_name', 'MBA')->count(),
            'BCA' => PprDetail::where('prog_name', 'BCA')->count(),
            'BBA' => PprDetail::where('prog_name', 'BBA')->count(),
        ];

        $isMainAdmin = self::isMainAdmin();
        $programMap = self::PROGRAM_MAP;

        return view('general.index', compact(
            'menus',
            'user',
            'documents',
            'activeProgram',
            'search',
            'counts',
            'isMainAdmin',
            'programMap'
        ));
    }

    /**
     * Admin Management view (Main Admin Only).
     */
    public function manage(Request $request)
    {
        if (! self::isMainAdmin()) {
            return redirect()->route('general.index')
                ->with('error', 'Unauthorized access. Only Main Admin can manage regulations.');
        }

        $user = (object) [
            'usercode' => session('user.usercode'),
            'role_id' => session('user.role_id'),
        ];
        $menus = MenuService::getMenusForUser($user);

        $documents = PprDetail::orderBy('created_at', 'desc')->paginate(20);
        $programMap = self::PROGRAM_MAP;

        return view('general.manage', compact(
            'menus',
            'user',
            'documents',
            'programMap'
        ));
    }

    /**
     * Upload a new regulation into visa.ppr_details (Main Admin Only).
     */
    public function store(Request $request)
    {
        if (! self::isMainAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'prog_name' => 'required|in:MCA,BBA,BCA,MBA',
            'regname' => 'required|string|max:255',
            'regcode' => 'required|string',
            'description' => 'nullable|string|max:2000',
            'document' => 'required|file|max:51200', // max 50MB, any file type allowed
        ]);

        // Validation for Regulation Code:
        // Automatically uppercase, must be exactly 3 characters, and 1st character must be 'R'
        $regcode = strtoupper(trim((string) $request->input('regcode')));
        if (strlen($regcode) !== 3 || ! str_starts_with($regcode, 'R')) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['regcode' => 'Regulation code must be exactly 3 characters starting with R (e.g. R22, R21, R24).']);
        }

        $progName = strtoupper(trim($request->input('prog_name')));
        $progId = self::PROGRAM_MAP[$progName]['id'] ?? null;

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');

        // Safe unique file name
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $storedFilename = time().'_'.$safeName.'.'.$extension;

        // Store into storage/app/public/general_documents
        $path = $file->storeAs('general_documents', $storedFilename, 'public');

        PprDetail::create([
            'prog_id' => $progId,
            'prog_name' => $progName,
            'regname' => trim($request->input('regname')),
            'regcode' => $regcode,
            'filename' => $originalName,
            'filepath' => $path,
            'description' => $request->input('description'),
        ]);

        return redirect()->back()
            ->with('success', "Regulation {$regcode} for {$progName} uploaded successfully!");
    }

    /**
     * Download document.
     */
    public function download(PprDetail $document)
    {
        if (! Storage::disk('public')->exists($document->filepath)) {
            return redirect()->back()->with('error', 'File not found on storage.');
        }

        $filePath = Storage::disk('public')->path($document->filepath);

        return response()->download($filePath, $document->filename);
    }

    /**
     * Preview document inline (PDF / Images).
     */
    public function preview(PprDetail $document)
    {
        if (! Storage::disk('public')->exists($document->filepath)) {
            return redirect()->back()->with('error', 'File not found on storage.');
        }

        $filePath = Storage::disk('public')->path($document->filepath);
        $mime = Storage::disk('public')->mimeType($document->filepath) ?: 'application/octet-stream';

        return response()->file($filePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$document->filename.'"',
        ]);
    }

    /**
     * Delete document (Main Admin Only).
     */
    public function destroy(PprDetail $document)
    {
        if (! self::isMainAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if (Storage::disk('public')->exists($document->filepath)) {
            Storage::disk('public')->delete($document->filepath);
        }

        $document->delete();

        return redirect()->back()
            ->with('success', 'Regulation document deleted successfully.');
    }

    /**
     * Legacy PPR Books alias.
     */
    public function pprBooks(Request $request)
    {
        return redirect()->route('general.index');
    }
}
