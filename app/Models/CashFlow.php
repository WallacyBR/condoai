<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope());
    }

    protected $fillable = [
        'condominium_id',
        'payable_id',
        'receivable_id',
        'date',
        'amount',
        'type',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'type' => TransactionType::class,
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
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
