<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'hrms.activity_logs';

    protected $fillable = [
        'user_code',
        'user_name',
        'role_id',
        'role_name',
        'module',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'device_type',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
