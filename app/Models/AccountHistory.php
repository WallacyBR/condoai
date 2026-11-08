<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'transaction_id',
        'reconciled_at',
        'notes',
    ];

    protected $casts = [
        'reconciled_at' => 'datetime',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
