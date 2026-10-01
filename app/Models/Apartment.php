<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use App\Traits\SmartOrdering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory, SmartOrdering;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'number',
        'floor',
        'condominium_id',
        'block_id',
        'tower_id',
        'owner_id',
        'tenant_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'floor' => 'integer',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function tower()
    {
        return $this->belongsTo(Tower::class);
    }

    public function owner()
    {
        return $this->belongsTo(Resident::class, 'owner_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Resident::class, 'tenant_id');
    }

    public function residents()
    {
        return $this->belongsToMany(Resident::class, 'apartment_resident')
            ->withPivot('type', 'active')
            ->withTimestamps();
    }

    public function allResidents()
    {
        return $this->residents()->wherePivot('type', 'resident');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Relaciona as vagas de estacionamento deste apartamento.
     */
    public function parkingSpots()
    {
        return $this->hasMany(ParkingSpot::class);
    }
}
