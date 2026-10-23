<?php

namespace Modules\Hrms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.departments';

    protected $primaryKey = 'department_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'department_id' => 'integer',
        'parent_id' => 'integer',
        'lock_status' => 'integer',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id', 'department_id');
    }
}
