<?php

namespace Modules\Hrms\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Auth\Models\Login;

class Employee extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'hrms.employees';

    protected $primaryKey = 'emp_code';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'designation_id' => 'integer',
        'department_id' => 'integer',
        'role_id' => 'integer',
        'is_active' => 'boolean',
        'date_of_join' => 'date',
        'log_timestamp' => 'datetime',
    ];

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id', 'designation_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_id', 'campus_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function login(): HasOne
    {
        return $this->hasOne(Login::class, 'usercode', 'emp_code');
    }

    public function assignedRoles(): HasMany
    {
        return $this->hasMany(RoleAssigned::class, 'emp_id', 'emp_code');
    }
}
