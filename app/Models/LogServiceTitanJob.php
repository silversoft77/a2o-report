<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogServiceTitanJob extends Model
{
    protected $table = 'log_service_titan_jobs';
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
