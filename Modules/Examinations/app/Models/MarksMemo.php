<?php

namespace Modules\Examinations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academics\Models\Subject;
use Modules\Admissions\Models\StudentRegistration;

// use Modules\Examinations\Database\Factories\MarksmemoFactory;

class MarksMemo extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'examinations.marks_cgpa';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [];

    public function scopeByRegno($query, string $regno)
    {

        return $query->where('regno', $regno);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subcode', 'subcode');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentRegistration::class, 'regno', 'register_no');
    }

    public function scopeByMmno($query, string $mmno)
    {

        return $query->where('mmno', $mmno);
    }
}
