<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceInstallmentGroup extends Model
{
    use HasFactory;

    protected $fillable = ['condominium_id', 'created_by', 'description', 'kind', 'total_amount', 'installments', 'first_due_date', 'expected_return_rate'];

    protected $casts = ['total_amount' => 'decimal:2', 'first_due_date' => 'date', 'expected_return_rate' => 'decimal:4'];

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    public function payables()
    {
        return $this->hasMany(Payable::class, 'installment_group_id');
    }
}
