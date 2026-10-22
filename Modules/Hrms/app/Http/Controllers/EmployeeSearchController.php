<?php

namespace Modules\Hrms\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeSearchController extends Controller
{
    use RequiresLogin;

    public function index(Request $request)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null && ! ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest')) {
            return view('errors.no-role', compact('user'));
        }

        $q = trim((string) $request->input('q', ''));
        $empType = $request->input('type');
        $status = $request->input('status');
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $query = DB::table('hrms.employees as e')
            ->select(
                'e.emp_code',
                'e.full_name',
                'e.ofc_mailid as email',
                'e.personal_mailid',
                'e.phone_primary as phone',
                'e.phone_secondary',
                'e.emp_type',
                'e.date_of_join',
                'e.is_active',
                'e.designation_id',
                'e.department_id'
            );

        // Multi-token semantic search across employee attributes
        if ($q !== '') {
            $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $token) {
                $query->where(function ($sub) use ($token) {
                    $term = "%{$token}%";
                    $sub->where('e.emp_code', 'ilike', $term)
                        ->orWhere('e.full_name', 'ilike', $term)
                        ->orWhere('e.ofc_mailid', 'ilike', $term)
                        ->orWhere('e.personal_mailid', 'ilike', $term)
                        ->orWhere('e.phone_primary', 'ilike', $term)
                        ->orWhere('e.phone_secondary', 'ilike', $term)
                        ->orWhere('e.emp_type', 'ilike', $term);
                });
            }
        }

        // Filter: Employment Type
        if (! empty($empType) && $empType !== 'all') {
            $query->where('e.emp_type', $empType);
        }

        // Filter: Active Status
        if (! empty($status) && $status !== 'all') {
            $isActive = $status === 'active' || $status === '1';
            $query->where('e.is_active', $isActive);
        }

        $employees = $query->orderBy('e.emp_code')
            ->paginate($perPage)
            ->withQueryString();

        // AJAX request: return JSON with rendered rows and pagination
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $rowsHtml = view('hrms::partials.employee-rows', compact('employees'))->render();
            $paginationHtml = ($employees->count() > 0 && $employees->hasPages())
                ? $employees->links('pagination::bootstrap-5')->render()
                : '';

            return response()->json([
                'success' => true,
                'html' => $rowsHtml,
                'pagination' => $paginationHtml,
                'total' => $employees->total(),
                'count' => $employees->count(),
                'from' => $employees->firstItem() ?? 0,
                'to' => $employees->lastItem() ?? 0,
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
            ]);
        }

        // Initial Filter Options
        $types = DB::table('hrms.employees')
            ->whereNotNull('emp_type')
            ->distinct()
            ->orderBy('emp_type')
            ->pluck('emp_type');

        $totalEmployees = DB::table('hrms.employees')->count();

        return view('hrms::empsearch', compact(
            'user',
            'menus',
            'employees',
            'q',
            'empType',
            'status',
            'perPage',
            'types',
            'totalEmployees'
        ));
    }
}
