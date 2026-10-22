<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuAccessRole extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.menu_access_roles';

    public $timestamps = false;

    protected $guarded = [];
}
