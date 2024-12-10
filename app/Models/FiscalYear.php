<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'from_date',
        'to_date',
        'from_date_nepali',
        'to_date_nepali',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'status' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($fiscalYear) {
            if ($fiscalYear->status) {
                static::where('id', '!=', $fiscalYear->id)
                      ->update(['status' => 0]);
            }
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
