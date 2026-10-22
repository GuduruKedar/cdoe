<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.menus';

    protected $primaryKey = 'menu_id';

    public $timestamps = false;

    protected $guarded = [];
}
