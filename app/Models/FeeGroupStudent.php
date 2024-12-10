<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeGroupStudent extends Model
{
    use HasFactory;

    protected $table = 'fee_group_student';

    protected $fillable = [
        'student_session_id',
        'fee_group_id',
    ];

    public function studentSession()
    {
        return $this->belongsTo(StudentSession::class);
    }

    public function feeGroup()
    {
        return $this->belongsTo(FeeGroup::class);
    }
}
