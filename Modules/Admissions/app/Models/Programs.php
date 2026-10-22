<?php

namespace Modules\Admissions\Models;

use Illuminate\Database\Eloquent\Model;

// use Modules\Admissions\Database\Factories\ProgramsFactory;

class Programs extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'master.programs';

    protected $primaryKey = 'program_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
}
