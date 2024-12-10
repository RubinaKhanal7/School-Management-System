<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeCollection extends Model
{
    protected $table = 'fee_collections';
    
    protected $fillable = [
        'student_session_id',
        'fee_groups_types_id',
        'amount',
        'payment_mode_id',
        'payed_on',
        'notes'
    ];

    protected $dates = [
        'payed_on',
        'created_at',
        'updated_at'
    ];

    public function feeGroupType()
    {
        return $this->belongsTo(FeeGroupType::class, 'fee_groups_types_id');
    }

    public function studentSession()
    {
        return $this->belongsTo(StudentSession::class);
    }

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class);
    }
}