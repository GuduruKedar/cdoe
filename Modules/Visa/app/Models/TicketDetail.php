<?php

namespace Modules\Visa\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Admissions\Models\StudentRegistration;
use Modules\Hrms\Models\Employee;

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

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_empcode', 'emp_code');
    }

    public function handlerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_userid', 'emp_code');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->std_tkt_status] ?? ('Code '.$this->std_tkt_status);
    }

    public function getStatusClassAttribute(): string
    {
        return self::STATUS_CLASSES[$this->std_tkt_status] ?? 'status-default';
    }

    public function getIsResolvedAttribute(): bool
    {
        return (int) $this->std_tkt_status === self::STATUS_RESOLVED;
    }

    /**
     * Get resolution duration in seconds (or time elapsed if still unresolved)
     */
    public function getDurationSecondsAttribute(): ?int
    {
        if (! $this->tkt_raised_datetime) {
            return null;
        }

        $raisedAt = Carbon::parse($this->tkt_raised_datetime);
        $endedAt = ($this->is_resolved && $this->emp_timestamp)
            ? Carbon::parse($this->emp_timestamp)
            : now();

        return max(0, (int) round($raisedAt->diffInSeconds($endedAt)));
    }

    /**
     * Format duration into human readable string: e.g. "2d 4h", "1h 30m", "45s"
     */
    public function getDurationFormattedAttribute(): string
    {
        $sec = $this->duration_seconds;
        if ($sec === null) {
            return '—';
        }

        if ($sec < 60) {
            return $sec.'s';
        }

        $days = (int) floor($sec / 86400);
        $remainder = $sec % 86400;
        $hours = (int) floor($remainder / 3600);
        $remainder %= 3600;
        $mins = (int) floor($remainder / 60);

        if ($days > 0) {
            return $hours > 0 ? "{$days}d {$hours}h" : "{$days}d";
        }

        if ($hours > 0) {
            return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
        }

        return "{$mins}m";
    }
}
