<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'name',
        'species',
        'breed',
        'age',
        'weight',
        'color',
        'is_vaccinated',
        'observations',
        'photo',
        'apartment_id',
        'resident_id',
        'condominium_id',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}
