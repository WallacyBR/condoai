<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Para o multi-tenancy

class MaintenanceArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'condominium_id',
    ];

    /**
     * The "booted" method of the model.
     *
     * Aplicar o escopo de condomínio globalmente.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    /**
     * Get the condominium that owns the maintenance area.
     */
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    /**
     * Get the equipment associated with the maintenance area.
     */
    public function equipments()
    {
        return $this->hasMany(MaintenanceEquipment::class);
    }
}
