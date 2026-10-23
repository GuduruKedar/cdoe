<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.menus';

    protected $primaryKey = 'menu_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'menu_id' => 'integer',
        'parent_id' => 'integer',
        'menu_status' => 'integer',
        'menu_level' => 'integer',
        'is_action' => 'integer',
        'project_id' => 'integer',
        'is_cdoe' => 'integer',
        'log_timestamp' => 'datetime',
    ];

    public function accessRoles(): HasMany
    {
        return $this->hasMany(MenuAccessRole::class, 'menu_code', 'menu_code');
    }
}
