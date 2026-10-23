<?php

namespace Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programs extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.programs';

    protected $primaryKey = 'program_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function students(): HasMany
    {
        return $this->hasMany(StudentRegistration::class, 'progid', 'program_id');
    }
}
