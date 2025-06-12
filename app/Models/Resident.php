<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CondominiumScope;

class Resident extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope());
    }

    protected $fillable = [
        'name',
        'phone',
        'email',
        'active',
        'condominium_id'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function ownedApartments()
    {
        return $this->hasMany(Apartment::class, 'owner_id');
    }

    public function rentedApartments()
    {
        return $this->hasMany(Apartment::class, 'tenant_id');
    }

    public function apartments()
    {
        return $this->belongsToMany(Apartment::class, 'apartment_resident')
            ->withPivot('type', 'active')
            ->withTimestamps();
    }

    public function residingApartments()
    {
        return $this->apartments()->wherePivot('type', 'resident');
    }
}
