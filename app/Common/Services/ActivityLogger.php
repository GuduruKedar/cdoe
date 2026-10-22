<?php

namespace App\Common\Services;

use App\Models\ActivityLog;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ActivityLogger
{
    /**
     * Record an activity log entry.
     *
     * @param  string  $module  e.g., AUTH, HRMS, ADMISSIONS, EXAMINATIONS, ACADEMICS, SYSTEM
     * @param  string  $action  e.g., LOGIN, LOGOUT, SWITCH_ROLE, SEARCH, CREATE, UPDATE, DELETE, VIEW
     * @param  string|null  $description  Human-readable description of the event
     * @param  array  $options  Optional overrides or extras (payload, status, user_code, etc.)
     */
    public static function log(
        string $module,
        string $action,
        ?string $description = null,
        array $options = []
    ): ?ActivityLog {
        try {
            $request = request();

            // Resolve user info from session or overrides
            $userCode = $options['user_code'] ?? Session::get('user.usercode');
            $userName = $options['user_name'] ?? Session::get('user.username') ?? Session::get('user.user_name');
            $roleId = $options['role_id'] ?? Session::get('user.role_id');
            $roleName = $options['role_name'] ?? null;

            if ($roleId && ! $roleName) {
                $role = Role::where('role_id', $roleId)->first();
                $roleName = $role ? $role->role_name : null;
            }

            // Detect device type / browser summary
            $userAgent = $request ? (string) $request->userAgent() : null;
            $deviceType = self::detectDeviceType($userAgent);

            return ActivityLog::create([
                'user_code' => $userCode ? (string) $userCode : null,
                'user_name' => $userName ? (string) $userName : null,
                'role_id' => $roleId ? (int) $roleId : null,
                'role_name' => $roleName,
                'module' => strtoupper($module),
                'action' => strtoupper($action),
                'description' => $description,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $userAgent,
                'device_type' => $deviceType,
                'status' => $options['status'] ?? 'success',
                'payload' => $options['payload'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ActivityLogger failed to record log: '.$e->getMessage(), [
                'module' => $module,
                'action' => $action,
            ]);

            return null;
        }
    }

    /**
     * Helper to detect simple device and browser profile from user agent.
     */
    public static function detectDeviceType(?string $ua): string
    {
        if (! $ua) {
            return 'Unknown Device';
        }

        $platform = 'Desktop';
        if (preg_match('/(android|iphone|ipad|ipod|mobile)/i', $ua)) {
            $platform = preg_match('/(ipad|tablet)/i', $ua) ? 'Tablet' : 'Mobile';
        }

        $os = 'Windows';
        if (preg_match('/windows nt 10/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        }

        $browser = 'Browser';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = 'Safari';
        }

        return "{$platform} ({$os}) • {$browser}";
    }
}
