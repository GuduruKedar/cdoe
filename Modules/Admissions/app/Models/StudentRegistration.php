<?php

namespace Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRegistration extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'admissions.student_registrations';

    protected $primaryKey = 'application_no';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Programs::class, 'progid', 'program_id');
    }

    public function electives(): BelongsTo
    {
        $table = $this->table;

        return $this->belongsTo(Electives::class, 'electiveid', 'elective_id');
    }
}
