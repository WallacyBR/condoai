<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Adicionado

class Visitor extends Model
{
    use HasFactory, SoftDeletes; // Adicionado SoftDeletes

    protected $fillable = [
        'name',
        'document',
        'phone',
        'email',
        'visitor_type',
        'company',
        'photo_url',
        'observations',
        'is_blocked', // Alterado de 'bloqueado'
        'condominium_id',
    ];

    protected $casts = [
        'is_blocked' => 'boolean', // Alterado de 'bloqueado'
    ];

    /**
     * Scope a query to only include visitors of a given condominium.
     */
    public function scopeCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(VisitorVehicle::class); // Alterado para VisitorVehicle
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class); // Alterado para AccessRequest
    }

    public function protectiveMeasuresAsAggressor(): HasMany
    {
        // Alterado para ProtectiveMeasure e 'aggressor_visitor_id'
        return $this->hasMany(ProtectiveMeasure::class, 'aggressor_visitor_id');
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}
