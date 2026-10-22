<?php

namespace Modules\Academics\Http\Controllers;

use App\Common\Services\MenuService;
use App\Common\Traits\RequiresLogin;
use App\Http\Controllers\Controller;

class AcademicController extends Controller
{
    use RequiresLogin;

    public function index()
    {
        $user = $this->requireLogin();
        $menus = MenuService::getMenusForUser($user);
        if ($menus === null) {
            return view('errors.no-role', compact('user'));
        }

        return view('academics::index', compact('user', 'menus'));
    }
}
