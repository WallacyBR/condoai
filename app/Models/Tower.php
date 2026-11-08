<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CondominiumScope;
use App\Models\Apartment;

class Tower extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope());
    }

    protected $fillable = [
        'name',
        'condominium_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }
}
