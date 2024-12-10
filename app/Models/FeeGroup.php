<?php

namespace App\Models;

use App\Models\FeeDue;
use App\Models\FeeGroupType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeGroup extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function feeGroupTypes()
    {
        return $this->hasMany(FeeGroupType::class, 'fee_group_id');
    }

    public function feeDues()
    {
        return $this->hasMany(FeeDue::class, 'fee_groups_id');
    }

   public function studentSessions()
{
    return $this->belongsToMany(StudentSession::class, 'fee_group_student', 'fee_group_id', 'student_session_id');
}
public function feeTypes()
{
    return $this->belongsToMany(FeeType::class, 'fee_groups_types')
                ->withPivot('amount', 'is_active', 'academic_session_id', 'school_id')
                ->withTimestamps();
}

public function students()
{
    return $this->belongsToMany(StudentSession::class, 'fee_group_student');
}
}