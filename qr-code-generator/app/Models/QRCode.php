<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRCode extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'qr_codes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_title',
        'description',
        'venue',
        'event_date',
        'event_time',
        'department',
        'qr_code',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'event_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this QR code
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
