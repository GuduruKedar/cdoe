<?php

namespace Modules\Auth\Models;

use App\Models\Role;
use App\Models\RoleAssigned;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Hrms\Models\Employee;

class Login extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.login';

    protected $primaryKey = 'usercode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'usercode', 'emp_code');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function rolesAssigned(): HasMany
    {
        return $this->hasMany(RoleAssigned::class, 'emp_id', 'usercode');
    }
}
