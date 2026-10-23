<?php

namespace Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDocument extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'admissions.student_documents';

    protected $primaryKey = 'doc_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'doc_id' => 'integer',
        'lock_status' => 'integer',
        'log_timestamp' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentRegistration::class, 'application_no', 'application_no');
    }

    public function isImage(): bool
    {
        return in_array(strtolower((string) $this->doctype), ['photo', 'avatar', 'image'], true);
    }
}
