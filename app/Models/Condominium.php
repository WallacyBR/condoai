<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Condominium extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Define o nome da tabela explicitamente para evitar pluralização incorreta.
     */
    protected $table = 'condominiums';

    protected $fillable = [
        'name',
        'document',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'responsible',
        'active',
        'domain',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'condominium_user')
            ->withTimestamps();
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function towers()
    {
        return $this->hasMany(Tower::class);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    public function apartments()
    {
        return $this->hasManyThrough(Apartment::class, Block::class);
    }

    public function parkingSpots()
    {
        return $this->hasMany(ParkingSpot::class);
    }

    public function identifierExists($type, $identifier)
    {
        switch ($type) {
            case 'tower':
                return $this->towers()->where('name', $identifier)->exists();
            case 'block':
                return $this->blocks()->where('name', $identifier)->exists();
            case 'apartment':
                return $this->apartments()->where('number', $identifier)->exists();
            default:
                return false;
        }
    }
}
