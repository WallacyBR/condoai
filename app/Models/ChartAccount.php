<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'condominium_id',
        'parent_id',
        'name',
        'type',
        'account_number',
    ];

    protected $casts = [
        'type' => AccountType::class,
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
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
