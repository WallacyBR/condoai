<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Ad extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'resident_id',
        'condominium_id',
        'title',
        'description',
        'price',
        'currency',
        'category',
        'condition',
        'location',
        'contact_info',
        'contact_phone',
        'contact_email',
        'status',
        'views_count',
        'reported_count',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the Ad.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the images for the Ad.
     */
    public function images()
    {
        return $this->hasMany(AdImage::class);
    }

    /**
     * Get the interactions for the Ad.
     */
    public function interactions()
    {
        return $this->hasMany(AdInteraction::class);
    }

    /**
     * Get the resident that owns the Ad.
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * Get the condominium of the Ad.
     */
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}