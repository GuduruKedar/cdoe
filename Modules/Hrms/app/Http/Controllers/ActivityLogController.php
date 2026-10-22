<?php

namespace Modules\Hrms\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
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
        $module = trim((string) $request->input('module', ''));
        $action = trim((string) $request->input('action', ''));
        $status = trim((string) $request->input('status', ''));
        $dateFilter = trim((string) $request->input('date', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [15, 20, 30, 50, 100], true)) {
            $perPage = 20;
        }

        $query = ActivityLog::query();

        // Search query
        if ($q !== '') {
            $like = '%'.strtolower($q).'%';
            $query->where(function ($sub) use ($like) {
                $sub->whereRaw('LOWER(user_code) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(user_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(module) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(action) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(ip_address) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(device_type) LIKE ?', [$like]);
            });
        }

        // Module filter
        if ($module !== '' && $module !== 'all') {
            $query->where('module', strtoupper($module));
        }

        // Action filter
        if ($action !== '' && $action !== 'all') {
            $query->where('action', strtoupper($action));
        }

        // Status filter
        if ($status !== '' && $status !== 'all') {
            $query->where('status', strtolower($status));
        }

        // Date filter
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        } elseif ($dateFilter === '7days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($dateFilter === '30days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        $logs = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        // Summary Statistics
        $todayStart = Carbon::today();
        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::where('created_at', '>=', $todayStart)->count(),
            'auth_today' => ActivityLog::where('created_at', '>=', $todayStart)->where('module', 'AUTH')->count(),
            'active_users' => ActivityLog::where('created_at', '>=', $todayStart)->whereNotNull('user_code')->distinct('user_code')->count('user_code'),
        ];

        // Available modules & actions for filter dropdowns
        $availableModules = ActivityLog::select('module')
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $availableActions = ActivityLog::select('action')
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Handle AJAX search requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $rowsHtml = view('hrms::partials.activity-rows', compact('logs'))->render();
            $paginationHtml = ($logs->count() > 0 && $logs->hasPages())
                ? $logs->links('pagination::bootstrap-5')->render()
                : '';

            return response()->json([
                'success' => true,
                'html' => $rowsHtml,
                'pagination' => $paginationHtml,
                'total' => $logs->total(),
                'count' => $logs->count(),
                'from' => $logs->firstItem() ?? 0,
                'to' => $logs->lastItem() ?? 0,
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'stats' => $stats,
            ]);
        }

        return view('hrms::activitylogs', compact(
            'logs',
            'stats',
            'availableModules',
            'availableActions',
            'menus',
            'user',
            'q',
            'module',
            'action',
            'status',
            'dateFilter',
            'perPage'
        ));
    }

    /**
     * Return single log details JSON for inspection modal.
     */
    public function show(int $id)
    {
        $this->requireLogin();

        $log = ActivityLog::findOrFail($id);

        return response()->json([
            'success' => true,
            'log' => $log,
            'created_at_human' => $log->created_at ? $log->created_at->diffForHumans() : '-',
            'created_at_full' => $log->created_at ? $log->created_at->format('M d, Y h:i:s A') : '-',
        ]);
    }
}
