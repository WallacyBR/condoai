<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'name',
        'condominium_id',
        'tower_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function tower()
    {
        return $this->belongsTo(Tower::class);
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }
}
