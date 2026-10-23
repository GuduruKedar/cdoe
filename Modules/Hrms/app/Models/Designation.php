<?php

namespace Modules\Hrms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.designations';

    protected $primaryKey = 'designation_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'designation_id' => 'integer',
        'designation_level' => 'integer',
        'lock_status' => 'integer',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'designation_id', 'designation_id');
    }
}
