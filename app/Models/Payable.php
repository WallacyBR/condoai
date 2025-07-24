<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payable extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope());
    }

    protected $fillable = [
        'condominium_id',
        'chart_account_id',
        'bank_id',
        'due_date',
        'amount',
        'status',
        'description',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartAccount::class, 'chart_account_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function attachments()
    {
        return $this->hasMany(PayableAttachment::class);
    }
}
