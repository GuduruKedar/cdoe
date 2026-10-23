<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Admissions\Models\StudentRegistration;

class CourseRegistration extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'academics.course_registration';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        's_year' => 'integer',
        's_sem' => 'integer',
        'reg_date' => 'date',
        'attendance_date' => 'date',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentRegistration::class, 'appno', 'application_no');
    }

    public function studentByRegno(): BelongsTo
    {
        return $this->belongsTo(StudentRegistration::class, 'regno', 'register_no');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subcode', 'subcode');
    }
}
