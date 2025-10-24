<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventName extends Model
{
    protected $table = 'event_names';
    public $timestamps = false;
    protected $fillable = ['name', 'display_on_client'];
}
