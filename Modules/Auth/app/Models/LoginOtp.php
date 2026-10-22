<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class LoginOtp extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.login_otp';

    protected $primaryKey = 'otp_id';

    public $timestamps = false;

    protected $guarded = [];
}
