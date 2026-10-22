<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.login';

    protected $primaryKey = 'usercode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];
}
