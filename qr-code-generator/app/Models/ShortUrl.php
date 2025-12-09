<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    protected $table = 'short_urls';

    protected $fillable = [
        'short_code',
        'event_title',
        'venue',
        'event_date',
        'event_time',
        'department',
        'description',
        'destination_url',
        'created_by',
        'status',
        'expires_at'
    ];

    protected $dates = ['event_date', 'expires_at'];

    // THIS RELATIONSHIP WAS MISSING → CAUSED 500 ERROR
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(\App\Models\Registration::class, 'short_code', 'short_code');
    }

    public function isActive()
    {
        return $this->status === 'active' &&
            ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
