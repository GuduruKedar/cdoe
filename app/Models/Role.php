<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Auth\Models\Login;
use Modules\Hrms\Models\Employee;

class Role extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.roles';

    protected $primaryKey = 'role_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'role_id' => 'integer',
        'role_status' => 'integer',
        'project_id' => 'integer',
        'log_timestamp' => 'datetime',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'role_id', 'role_id');
    }

    public function logins(): HasMany
    {
        return $this->hasMany(Login::class, 'role_id', 'role_id');
    }

    public function accessRoles(): HasMany
    {
        return $this->hasMany(MenuAccessRole::class, 'role_id', 'role_id');
    }
}
