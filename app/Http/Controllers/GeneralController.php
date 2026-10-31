<?php

namespace App\Http\Controllers;

use App\Common\Services\MenuService;
use App\Models\GeneralDocument;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeneralController extends Controller
{
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
     * Main General Regulations & Academic Documents Hub (Cards View).
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
        $activeSubCategory = trim($request->query('sub', 'All'));
        $search = trim($request->query('q', ''));

        $query = GeneralDocument::query()->where('status', 1);

        // Filter by program (MBA / BBA / COMMON)
        if ($activeProgram && $activeProgram !== 'ALL') {
            $query->where(function ($q) use ($activeProgram) {
                $q->where('program', $activeProgram)
                    ->orWhere('program', 'COMMON');
            });
        }

        // Filter by dynamic subcategory (e.g. Regulations, Syllabus, etc.)
        if ($activeSubCategory && $activeSubCategory !== 'All') {
            $query->where('sub_category', $activeSubCategory);
        }

        // Search in title, code, description, filename
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                    ->orWhere('regulation_code', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%")
                    ->orWhere('file_name', 'ILIKE', "%{$search}%");
            });
        }

        $documents = $query->orderBy('program')
            ->orderBy('regulation_code', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate dynamic program counts
        $counts = [
            'ALL' => GeneralDocument::where('status', 1)->count(),
            'MBA' => GeneralDocument::where('status', 1)->whereIn('program', ['MBA', 'COMMON'])->count(),
            'BBA' => GeneralDocument::where('status', 1)->whereIn('program', ['BBA', 'COMMON'])->count(),
        ];

        // Retrieve all distinct subcategories currently registered in the DB
        $allSubCategories = GeneralDocument::where('status', 1)
            ->distinct()
            ->pluck('sub_category')
            ->filter()
            ->values()
            ->toArray();

        // Default standard subcategories if DB is initially empty
        if (empty($allSubCategories)) {
            $allSubCategories = ['Regulations', 'Syllabus', 'Exam Guidelines', 'Academic Calendar'];
        }

        $isMainAdmin = self::isMainAdmin();

        return view('general.index', compact(
            'menus',
            'user',
            'documents',
            'activeProgram',
            'activeSubCategory',
            'search',
            'counts',
            'allSubCategories',
            'isMainAdmin'
        ));
    }

    /**
     * Admin Management view (Main Admin Only).
     */
    public function manage(Request $request)
    {
        if (! self::isMainAdmin()) {
            return redirect()->route('general.index')
                ->with('error', 'Unauthorized access. Only Main Admin can manage regulations and documents.');
        }

        $user = (object) [
            'usercode' => session('user.usercode'),
            'role_id' => session('user.role_id'),
        ];
        $menus = MenuService::getMenusForUser($user);

        $documents = GeneralDocument::orderBy('created_at', 'desc')->paginate(20);

        $existingSubCategories = GeneralDocument::distinct()
            ->pluck('sub_category')
            ->filter()
            ->values()
            ->toArray();

        return view('general.manage', compact(
            'menus',
            'user',
            'documents',
            'existingSubCategories'
        ));
    }

    /**
     * Upload a new regulation / document (Main Admin Only).
     */
    public function store(Request $request)
    {
        if (! self::isMainAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'program' => 'required|in:MBA,BBA,COMMON',
            'title' => 'required|string|max:255',
            'regulation_code' => 'nullable|string|max:50',
            'sub_category' => 'nullable|string|max:100',
            'new_sub_category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'document' => 'required|file|max:51200', // max 50MB, any file type allowed
        ]);

        // Resolve sub-category (either custom newly typed or selected from dropdown)
        $subCategory = trim($request->input('new_sub_category') ?: $request->input('sub_category') ?: 'Regulations');

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $fileSize = $file->getSize();

        // Generate safe unique filename
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $storedFilename = time().'_'.$safeName.'.'.$extension;

        // Store into storage/app/public/general_documents
        $path = $file->storeAs('general_documents', $storedFilename, 'public');

        GeneralDocument::create([
            'program' => strtoupper($request->input('program')),
            'sub_category' => $subCategory,
            'title' => trim($request->input('title')),
            'regulation_code' => $request->input('regulation_code') ? strtoupper(trim($request->input('regulation_code'))) : null,
            'description' => $request->input('description'),
            'file_path' => $path,
            'file_name' => $originalName,
            'file_type' => $extension,
            'file_size' => $fileSize,
            'uploaded_by' => session('user.usercode', 'Main Admin'),
            'downloads_count' => 0,
            'status' => 1,
        ]);

        return redirect()->back()
            ->with('success', 'Document uploaded and published successfully!');
    }

    /**
     * Download document.
     */
    public function download(GeneralDocument $document)
    {
        if (! Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'File not found on storage.');
        }

        $document->increment('downloads_count');

        $filePath = Storage::disk('public')->path($document->file_path);

        return response()->download($filePath, $document->file_name);
    }

    /**
     * Preview document inline (PDF / Images).
     */
    public function preview(GeneralDocument $document)
    {
        if (! Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'File not found on storage.');
        }

        $filePath = Storage::disk('public')->path($document->file_path);
        $mime = Storage::disk('public')->mimeType($document->file_path) ?: 'application/octet-stream';

        return response()->file($filePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$document->file_name.'"',
        ]);
    }

    /**
     * Delete document (Main Admin Only).
     */
    public function destroy(GeneralDocument $document)
    {
        if (! self::isMainAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->back()
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Legacy PPR Books alias.
     */
    public function pprBooks(Request $request)
    {
        return redirect()->route('general.index', ['sub' => 'PPR Books']);
    }
}
