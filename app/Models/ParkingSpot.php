<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSpot extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'identifier',
        'condominium_id',
        'apartment_id',
        'rented_to_apartment_id',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function rentedToApartment()
    {
        return $this->belongsTo(Apartment::class, 'rented_to_apartment_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function solicitacoesAcesso()
    {
        return $this->hasMany(SolicitacaoAcesso::class, 'vaga_utilizada_id');
    }
}
