<?php

namespace Modules\Visa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Admissions\Models\StudentRegistration;

class TicketDetail extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'visa.tickets_details';

    protected $primaryKey = 'tkt_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'std_tkt_status' => 'integer',
        'emp_tkt_status' => 'integer',
        'tkt_raised_datetime' => 'datetime',
        'std_timestamp' => 'datetime',
        'emp_timestamp' => 'datetime',
    ];

    public const STATUS_RAISED = 0;

    public const STATUS_PENDING = 1;

    public const STATUS_IN_PROCESS = 2;

    public const STATUS_RESOLVED = 3;

    public const STATUS_LABELS = [
        self::STATUS_RAISED => 'Raised',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_IN_PROCESS => 'In Process',
        self::STATUS_RESOLVED => 'Resolved',
    ];

    public const STATUS_CLASSES = [
        self::STATUS_RAISED => 'status-raised',
        self::STATUS_PENDING => 'status-pending',
        self::STATUS_IN_PROCESS => 'status-in-process',
        self::STATUS_RESOLVED => 'status-resolved',
    ];

    protected $appends = [
        'status_label',
        'status_class',
    ];

    public function ticketMaster(): BelongsTo
    {
        return $this->belongsTo(TicketMaster::class, 'tkt_head_id', 'tkt_head_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentRegistration::class, 'regno', 'register_no');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->std_tkt_status] ?? ('Code '.$this->std_tkt_status);
    }

    public function getStatusClassAttribute(): string
    {
        return self::STATUS_CLASSES[$this->std_tkt_status] ?? 'status-default';
    }
}
