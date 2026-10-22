<?php

namespace App\Common\Traits;

use Illuminate\Support\Facades\Session;
use Modules\Auth\Models\Login;

trait RequiresLogin
{
    protected function requireLogin(): Login
    {
        if (! Session::has('user.usercode')) {
            abort(redirect()->route('login'));
        }

        $user = Login::where('usercode', Session::get('user.usercode'))->first();
        if (! $user) {
            abort(redirect()->route('login'));
        }

        return $user;
    }
}
