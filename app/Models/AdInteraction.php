<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdInteraction extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ad_id',
        'user_id',
        'interaction_type',
        'interaction_timestamp',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'interaction_timestamp' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the ad that the interaction belongs to.
     */
    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    /**
     * Get the user that performed the interaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
