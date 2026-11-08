<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebauthnChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'user_id',
        'condominium_id',
        'email',
        'type',
        'challenge',
        'options',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'options' => 'array',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function markUsed(): void
    {
        $this->forceFill(['used_at' => now()])->save();
    }
}
