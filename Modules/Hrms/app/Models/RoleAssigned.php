<?php

namespace Modules\Hrms\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleAssigned extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.roles_assigned';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'roles_role_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_access_date' => 'datetime',
        'log_timestamp' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'roles_role_id', 'role_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'emp_code');
    }
}
