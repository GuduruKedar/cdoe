<?php

namespace Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;

// use Modules\Academics\Database\Factories\SubjectFactory;

class Subject extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'academics.subjects';

    protected $primaryKey = 'subcode';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): SubjectFactory
    // {
    //     // return SubjectFactory::new();
    // }
}
