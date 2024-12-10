<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $table = 'accounts';

    protected $fillable = [
        'name',
        'code',
        'expense_header_no',
        'status',
        'created_by',
        'updated_by',
        'opening_balance',
        'balance_type',
    ];

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }


}
