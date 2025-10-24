<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEvent extends Model
{
    protected $table = 'log_events';
    public $timestamps = false;
    protected $fillable = ['market_id', 'event_name_id', 'session_id', 'data', 'created_at'];

    public function eventName()
    {
        return $this->belongsTo(EventName::class, 'event_name_id');
    }

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id');
    }
}
