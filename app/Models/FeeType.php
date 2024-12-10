<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'code', 'description', 'is_active',
    ];

    public function feeGroups()
    {
        return $this->belongsToMany(FeeGroup::class, 'fee_groups_types')
                    ->withPivot('amount', 'is_active', 'academic_session_id', 'school_id')
                    ->withTimestamps();
    }
}
