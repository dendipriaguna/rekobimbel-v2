<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPreference extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'jenjang',
        'max_price',
        'gender',
        'availability',
        'location',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
