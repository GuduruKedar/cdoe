<?php

namespace Modules\Auth\Http\Controllers;

use App\Common\Services\ActivityLogger;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Modules\Auth\Mail\OtpMail;
use Modules\Auth\Models\Login;
use Modules\Auth\Models\LoginOtp;

class AuthController extends Controller
{
    /* ==========================================================
     |  SHOW LOGIN
     ========================================================== */
    public function showLogin()
    {
        //        $val = config("auth.dev_auto_login",false);

        return view('auth::login');
    }

    /* ==========================================================
     |  SEND OTP
     |  - DEV  mode: skip verify, log in now
     |  - PROD mode: send email + go to verify
     ========================================================== */
    public function sendOtp(Request $request)
    {
        $request->validate(['usercode' => 'required|string|max:50']);
        $usercode = trim($request->usercode);

        $user = Login::where('usercode', $usercode)->first();
        if (! $user) {
            return back()->with('error', 'Invalid User Code.');
        }
        if (app()->environment('local') && filter_var(config('auth.dev_auto_login', false), FILTER_VALIDATE_BOOLEAN)) {
            //            dd([
            //                'environment' => app()->environment(),
            //                'config_value' => config('auth.dev_auto_login'),
            //                'boolean_value' => filter_var(
            //                    config('auth.dev_auto_login', false),
            //                    FILTER_VALIDATE_BOOLEAN
            //                ),
            //            ]);
            return $this->loginUser($user);
        }
        // Role-based email selection
        if ((int) $user->user_type === 1) {
            $toEmail = $user->personalmail;
        } elseif ((int) $user->user_type === 2) {
            $toEmail = $user->officialmail;
        } else {
            $toEmail = $user->officialmail ?: $user->personalmail;
        }

        if (! $toEmail) {
            return back()->with('error', 'No email registered for your account type.');
        }

        $otp = self::generateOtp();
        $now = Carbon::now();
        $expires = $now->copy()->addMinutes(5);

        // Invalidate old unused OTPs
        LoginOtp::where('usercode', $usercode)
            ->where('purpose', 'login')
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Save new OTP
        LoginOtp::create([
            'usercode' => $usercode,
            'identifier' => $toEmail,
            'otp_code' => $otp,
            'purpose' => 'login',
            'is_used' => false,
            'expires_at' => $expires,
            'attempt_count' => 0,
            'log_userid' => $usercode,
            'log_ipaddress' => $request->ip(),
            'log_timestamp' => $now,
        ]);

        Session::put('otp_usercode', $usercode);
        Session::put('otp_email', $toEmail);

        // PROD: send email, go to verify
        Mail::to($toEmail)->send(new OtpMail($otp, $usercode));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to: '.$this->maskEmail($toEmail),
        ]);
    }

    /* ==========================================================
     |  SHOW VERIFY (prod only)
     ========================================================== */
    public function showVerify()
    {
        if (! Session::has('otp_usercode')) {
            return redirect()->route('login');
        }

        return view('auth::verify');
    }

    /* ==========================================================
     |  VERIFY OTP (prod only)
     ========================================================== */
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $usercode = Session::get('otp_usercode');
        if (! $usercode) {
            return redirect()->route('login');
        }

        $row = LoginOtp::where('usercode', $usercode)
            ->where('purpose', 'login')
            ->orderByDesc('otp_id')
            ->first();

        if (! $row) {
            return back()->with('error', 'No OTP found. Request a new one.');
        }
        if ($row->is_used) {
            return back()->with('error', 'This OTP has already been used. Request a new one.');
        }
        if ((int) $row->attempt_count >= 5) {
            $row->is_used = true;
            $row->save();

            return redirect()->route('login')->with('error', 'Too many attempts.');
        }
        if (Carbon::parse($row->expires_at)->isPast()) {
            $row->is_used = true;
            $row->save();

            return back()->with('error', 'OTP expired.');
        }
        if (trim((string) $row->otp_code) !== trim((string) $request->otp)) {
            $row->attempt_count = (int) $row->attempt_count + 1;
            $row->log_ipaddress = $request->ip();
            $row->log_timestamp = Carbon::now();
            $row->save();

            return back()->with('error', 'Invalid OTP.');
        }

        // Success
        $row->is_used = true;
        $row->log_ipaddress = $request->ip();
        $row->log_timestamp = Carbon::now();
        $row->save();

        $user = Login::where('usercode', $usercode)->first();
        if (! $user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        return $this->loginUser($user);
    }

    /* ==========================================================
     |  LOGOUT
     ========================================================== */
    public function logout()
    {
        $userCode = Session::get('user.usercode');
        $roleId = Session::get('user.role_id');
        if ($userCode) {
            ActivityLogger::log('AUTH', 'LOGOUT', "User {$userCode} logged out.", [
                'user_code' => $userCode,
                'role_id' => $roleId,
                'status' => 'info',
            ]);
        }

        Session::flush();

        return redirect()->route('login')->with('success', 'Logged out.');
    }

    /* ==========================================================
     |  SHARED LOGIN STEP — NO role logic here
     ========================================================== */
    private function loginUser(Login $user)
    {
        Session::put('user', [
            'usercode' => $user->usercode,
            'user_type' => $user->user_type,
            'role_id' => $user->role_id,     // from master.login
        ]);

        ActivityLogger::log('AUTH', 'LOGIN', "User {$user->usercode} authenticated and logged in successfully.", [
            'user_code' => $user->usercode,
            'role_id' => $user->role_id,
            'status' => 'success',
        ]);

        Session::forget(['otp_usercode', 'otp_email']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->route('dashboard');
    }

    /* ==========================================================
     |  HELPERS
     ========================================================== */
    private static function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email);

        return substr($name, 0, 2)
            .str_repeat('*', max(strlen($name) - 2, 2))
            .'@'.$domain;
    }
}
