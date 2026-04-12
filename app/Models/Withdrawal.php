<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'teacher_profile_id',
        'amount',
        'status',
        'proof_image',
        'bank_name',
        'bank_account_number',
        'bank_account_name'
    ];

    public function teacherProfile()
    {
        return $this->belongsTo(TeacherProfile::class);
    }
}
