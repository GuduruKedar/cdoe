<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuAccessRole extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.menu_access_roles';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'role_id' => 'integer',
        'is_cdoe' => 'integer',
        'log_timestamp' => 'datetime',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_code', 'menu_code');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
