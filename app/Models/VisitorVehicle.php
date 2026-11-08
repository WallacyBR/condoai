<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Adicionado

class VisitorVehicle extends Model
{
    use HasFactory, SoftDeletes; // Adicionado SoftDeletes

    protected $fillable = [
        'visitor_id',
        'brand',
        'model',
        'color',
        'license_plate',
        'plate', // Mantido para compatibilidade
        'condominium_id',
    ];

    /**
     * Scope a query to only include visitor vehicles of a given condominium.
     */
    public function scopeCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class); // Alterado para Visitor
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class); // Alterado para AccessRequest
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}
