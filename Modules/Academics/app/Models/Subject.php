<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Examinations\Models\MarksMemo;

class Subject extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'academics.subjects';

    protected $primaryKey = 'subcode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function courseRegistrations(): HasMany
    {
        return $this->hasMany(CourseRegistration::class, 'subcode', 'subcode');
    }

    public function coPoPsoMappings(): HasMany
    {
        return $this->hasMany(CoPoPsoMapping::class, 'subcode', 'subcode');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(MarksMemo::class, 'subcode', 'subcode');
    }
}
