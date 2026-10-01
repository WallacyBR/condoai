<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPasskey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'condominium_id',
        'name',
        'credential_id',
        'credential_id_hash',
        'credential_public_key',
        'credential_record',
        'user_handle',
        'transports',
        'sign_count',
        'backup_eligible',
        'backup_status',
        'last_used_at',
        'revoked_at',
        'revoked_by',
    ];

    protected $casts = [
        'credential_record' => 'array',
        'transports' => 'array',
        'backup_eligible' => 'boolean',
        'backup_status' => 'boolean',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $hidden = [
        'credential_id',
        'credential_id_hash',
        'credential_public_key',
        'credential_record',
        'user_handle',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function revoke(User $actor): void
    {
        $this->forceFill([
            'revoked_at' => now(),
            'revoked_by' => $actor->id,
        ])->save();
    }
}
