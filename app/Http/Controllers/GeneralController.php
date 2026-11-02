<?php

namespace App\Http\Controllers;

use App\Common\Services\MenuService;
use App\Models\MenuAccessRole;
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
     * Check if the current user can manage (upload/delete) PPR Books / Regulations.
     * Allowed if user is Main Admin OR role has menu 143 (PPR Books upload).
     */
    public static function canManage(): bool
    {
        if (self::isMainAdmin()) {
            return true;
        }

        $roleId = (int) session('user.role_id');
        if (! $roleId) {
            return false;
        }

        return MenuAccessRole::where('role_id', $roleId)->where('menu_id', 143)->exists();
    }

    /**
     * Get available month & year options (combining presets with dynamically saved entries).
     */
    public static function getAvailableMonthYears(): array
    {
        $presets = [
            'Jan 2022',
            'July 2022',
            'Jan 2023',
            'July 2023',
            'Jan 2024',
            'July 2024',
            'Jan 2025',
            'July 2025',
            'Jan 2026',
            'July 2026',
        ];

        try {
            $dbMonthYears = PprDetail::whereNotNull('month_year')
                ->where('month_year', '!=', '')
                ->distinct()
                ->pluck('month_year')
                ->toArray();

            return array_values(array_unique(array_merge($presets, $dbMonthYears)));
        } catch (\Throwable $e) {
            return $presets;
        }
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
        $activeMonthYear = trim($request->query('month_year', 'ALL'));
        $search = trim($request->query('q', ''));

        $query = PprDetail::query();

        // Filter by program (MCA / BBA / BCA / MBA)
        if ($activeProgram && $activeProgram !== 'ALL') {
            $query->where('prog_name', $activeProgram);
        }

        // Filter by month & year (e.g. Jan 2022, July 2022)
        if ($activeMonthYear && $activeMonthYear !== 'ALL') {
            $query->where('month_year', $activeMonthYear);
        }

        // Search in title, code, month_year, description, filename
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('regname', 'ILIKE', "%{$search}%")
                    ->orWhere('regcode', 'ILIKE', "%{$search}%")
                    ->orWhere('month_year', 'ILIKE', "%{$search}%")
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

        $isMainAdmin = self::canManage();
        $programMap = self::PROGRAM_MAP;
        $monthYears = self::getAvailableMonthYears();

        return view('general.index', compact(
            'menus',
            'user',
            'documents',
            'activeProgram',
            'activeMonthYear',
            'search',
            'counts',
            'isMainAdmin',
            'programMap',
            'monthYears'
        ));
    }

    /**
     * Admin Management view (Main Admin / Authorized Roles Only).
     */
    public function manage(Request $request)
    {
        if (! self::canManage()) {
            return redirect()->route('general.pprbooks')
                ->with('error', 'Unauthorized access. Only authorized roles can manage PPR Books.');
        }

        $user = (object) [
            'usercode' => session('user.usercode'),
            'role_id' => session('user.role_id'),
        ];
        $menus = MenuService::getMenusForUser($user);

        $documents = PprDetail::orderBy('created_at', 'desc')->paginate(20);
        $programMap = self::PROGRAM_MAP;
        $monthYears = self::getAvailableMonthYears();

        return view('general.manage', compact(
            'menus',
            'user',
            'documents',
            'programMap',
            'monthYears'
        ));
    }

    /**
     * Upload a new regulation into visa.ppr_details (Main Admin / Authorized Roles Only).
     */
    public function store(Request $request)
    {
        if (! self::canManage()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'prog_name' => 'required|in:MCA,BBA,BCA,MBA',
            'regname' => 'required|string|max:255',
            'regcode' => 'required|string',
            'month_year' => 'nullable|string|max:100',
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
            'month_year' => trim($request->input('month_year') ?: '') ?: null,
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
     * Delete document (Main Admin / Authorized Roles Only).
     */
    public function destroy(PprDetail $document)
    {
        if (! self::canManage()) {
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
     * Direct alias for PPR Books view.
     */
    public function pprBooks(Request $request)
    {
        return $this->index($request);
    }
}
