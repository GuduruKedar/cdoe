<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoPoPsoMapping extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'academics.co_po_pso_mapping';

    protected $primaryKey = 'srno';

    public $timestamps = false;

    protected $guarded = [];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subcode', 'subcode');
    }
}
