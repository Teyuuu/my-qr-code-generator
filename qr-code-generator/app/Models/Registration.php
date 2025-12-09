<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $table = 'registrations';

    protected $fillable = [
        'short_code', 'firstname', 'middlename', 'lastname',
        'lgu_company', 'position', 'contact', 'purpose'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($reg) {
            $reg->firstname   = strip_tags(trim($reg->firstname));
            $reg->middlename  = $reg->middlename ? strip_tags(trim($reg->middlename)) : null;
            $reg->lastname    = strip_tags(trim($reg->lastname));
            $reg->lgu_company = strip_tags(trim($reg->lgu_company));
            $reg->position    = strip_tags(trim($reg->position));
            $reg->contact     = preg_replace('/[^0-9+()-]/', '', $reg->contact);
            $reg->purpose     = strip_tags(trim($reg->purpose));
        });
    }

    public function shortUrl()
    {
        return $this->belongsTo(ShortUrl::class, 'short_code', 'short_code');
    }
}
