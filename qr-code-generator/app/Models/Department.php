<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'head',
    ];

    /**
     * Get the users (staff) that belong to this department
     */
    public function users()
    {
        return $this->hasMany(User::class, 'department', 'name');
    }
}
