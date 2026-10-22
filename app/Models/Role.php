<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.roles';

    protected $primaryKey = 'role_id';

    public $timestamps = false;

    protected $guarded = [];
}
