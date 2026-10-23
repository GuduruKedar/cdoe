<?php

namespace Modules\Hrms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.campuses';

    protected $primaryKey = 'campus_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'campus_country_id' => 'integer',
        'lock_status' => 'integer',
        'log_timestamp' => 'datetime',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'campus_id', 'campus_id');
    }
}
