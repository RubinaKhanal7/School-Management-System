<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'description',
        'voucher_type_id',
        'status',
        'transaction_date_eng',
        'transaction_date_nepali',
        'fiscal_year_id',
        'transaction_amount',
        'created_by',
        'updated_by',
        'is_opening_balance'
    ];

    protected $casts = [
        'status' => 'boolean',
        'transaction_amount' => 'decimal:2',
        'transaction_date_eng' => 'date:Y-m-d',
    ];

    public function voucherType()
    {
        return $this->belongsTo(VoucherType::class);
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
