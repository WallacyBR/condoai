<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'chart_account_id',
        'condominium_id',
        'created_by',
        'date',
        'amount',
        'type',
        'status',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'type' => TransactionType::class,
    ];

    public function account()
    {
        return $this->belongsTo(ChartAccount::class, 'chart_account_id');
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
