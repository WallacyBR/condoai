<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'payable_id',
        'receivable_id',
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

    public function payable()
    {
        return $this->belongsTo(Payable::class);
    }

    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }
}
