<?php

namespace Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;

// use Modules\Admissions\Database\Factories\ElectivesFactory;

class Electives extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.electives';

    protected $primaryKey = 'elective_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
}
