<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Market extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'domain',
        'path',
        'time_zone_id',
        'latest_unavailability'
    ];

    /**
     * Attribute casting
     *
     * @var array<string,string>
     */
    protected $casts = [
        'time_zone_id' => 'integer',
        'latest_unavailability' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'market_user');
    }
}
