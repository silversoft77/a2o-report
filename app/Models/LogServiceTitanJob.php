<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogServiceTitanJob extends Model
{
    use SoftDeletes;

    protected $table = 'log_service_titan_jobs';
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'market_id',
        'service_titan_job_id',
        'business_unit_id',
        'job_type_id',
        'tag_type_ids',
        'technician_id',
        'campaign_id',
        'start',
        'end',
        'summary',
        'chargebee',
        'web_session_data',
        'attributions_sent',
        'job_status',
        's2f',
        'created_at',
        'updated_at',
        'deleted_at',
        'referral_id',
    ];

    public function market()
    {
        return $this->belongsTo(Market::class);
    }
}
