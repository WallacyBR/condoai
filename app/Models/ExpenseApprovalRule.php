<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseApprovalRule extends Model
{
    use HasFactory;

    protected $fillable = ['condominium_id', 'profile_id', 'user_id', 'minimum_amount', 'maximum_amount', 'approval_order', 'active'];

    protected $casts = ['minimum_amount' => 'decimal:2', 'maximum_amount' => 'decimal:2', 'active' => 'boolean'];

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
