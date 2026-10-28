<?php

namespace Modules\Visa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMaster extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'visa.ticket_master';

    protected $primaryKey = 'sno';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'sno' => 'integer',
        'lock_status' => 'integer',
        'log_timestamp' => 'datetime',
    ];

    public function ticketDetails(): HasMany
    {
        return $this->hasMany(TicketDetail::class, 'tkt_head_id', 'tkt_head_id');
    }
}
