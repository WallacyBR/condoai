<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceSettlement extends Model
{
    use HasFactory;

    protected $table = 'financial_settlements';

    protected $fillable = [
        'condominium_id', 'payable_id', 'receivable_id', 'bank_id', 'created_by',
        'settled_on', 'amount', 'reference', 'notes', 'proof_path',
    ];

    protected $casts = ['settled_on' => 'date', 'amount' => 'decimal:2'];

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    public function payable()
    {
        return $this->belongsTo(Payable::class);
    }

    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
