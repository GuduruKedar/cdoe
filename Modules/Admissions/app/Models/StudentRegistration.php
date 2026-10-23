<?php

namespace Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academics\Models\CourseRegistration;
use Modules\Examinations\Models\MarksMemo;

class StudentRegistration extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'admissions.student_registrations';

    protected $primaryKey = 'application_no';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'progid' => 'integer',
        'electiveid' => 'integer',
        'admyear' => 'integer',
        'lock_status' => 'integer',
        'dob' => 'date',
        'doj' => 'date',
        'log_timestamp' => 'datetime',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Programs::class, 'progid', 'program_id');
    }

    public function electives(): BelongsTo
    {
        return $this->belongsTo(Electives::class, 'electiveid', 'elective_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class, 'application_no', 'application_no');
    }

    public function courseRegistrations(): HasMany
    {
        return $this->hasMany(CourseRegistration::class, 'appno', 'application_no');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(MarksMemo::class, 'regno', 'register_no');
    }

    public function getProgramShortNameAttribute(): ?string
    {
        return $this->program?->program_short_name;
    }
}
