<?php

namespace App\Models;

use App\Enums\PersonType;
use App\Enums\ResidentClassification;
use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'name',
        'rg',
        'cpf',
        'cell_phone',
        'commercial_phone',
        'whatsapp',
        'photo',
        'person_type',
        'notes',
        'is_porter',
        'is_janitor',
        'is_cleaning',
        'apartment_id',
        'email',
        'billing_address_type',
        'cep',
        'address',
        'neighborhood',
        'city',
        'state',
        'classification',
        'active',
        'condominium_id',
        'user_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'classification' => ResidentClassification::class,
        'person_type' => PersonType::class,
        'is_porter' => 'boolean',
        'is_janitor' => 'boolean',
        'is_cleaning' => 'boolean',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ownedApartments()
    {
        return $this->hasMany(Apartment::class, 'owner_id');
    }

    public function rentedApartments()
    {
        return $this->hasMany(Apartment::class, 'tenant_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
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

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    // Métodos auxiliares para verificar classificações
    public function isProprietario(): bool
    {
        return $this->classification === ResidentClassification::PROPRIETARIO;
    }

    public function isMorador(): bool
    {
        return $this->classification === ResidentClassification::MORADOR;
    }

    public function isInquilino(): bool
    {
        return $this->classification === ResidentClassification::INQUILINO;
    }

    public function isProprietarioMorador(): bool
    {
        return $this->classification === ResidentClassification::PROPRIETARIO;
    }

    public function getClassificationLabel(): string
    {
        return $this->classification->label();
    }

    public function getPersonTypeLabel(): string
    {
        return $this->person_type->label();
    }

    public function getRawClassificationAttribute()
    {
        return $this->classification->value;
    }
}
