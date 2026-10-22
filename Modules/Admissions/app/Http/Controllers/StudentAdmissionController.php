<?php

namespace Modules\Admissions\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Modules\Admissions\Models\StudentRegistration;

class StudentAdmissionController extends Controller
{
    use RequiresLogin;

    /** Screen: Admission Info */
    public function info($regno = null)
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        $registerNo = $regno ?? Session::get('user.usercode');
        $student = StudentRegistration::where('register_no', $registerNo)->with(['program:program_id,program_code', 'electives:elective_id,elective_short_name'])->first();

        return view('admissions::stdadmissioninfo', compact('user', 'menus', 'student', 'registerNo'));
    }

    public function studentDetails($registerNo)
    {
        $this->requireLogin();
        $student = StudentRegistration::where('register_no', $registerNo)
            ->with(['program:program_id,program_code,program_long_name', 'electives:elective_id,elective_short_name'])
            ->first();

        return response()->json($student);
    }

    // Next screens → add methods here:
    // public function payFee()      { ... }
    // public function uploadDoc()   { ... }
    // public function status()      { ... }
    // public function application() { ... }
}
