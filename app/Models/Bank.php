<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope());
    }

    protected $fillable = [
        'condominium_id',
        'name',
        'agency',
        'account_number',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function payables()
    {
        return $this->hasMany(Payable::class);
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }
}
