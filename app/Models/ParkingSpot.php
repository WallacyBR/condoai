<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CondominiumScope;

class ParkingSpot extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope());
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
}
