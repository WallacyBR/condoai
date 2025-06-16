<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CondominiumScope;

class Visitor extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CondominiumScope());
    }

    protected $fillable = [
        'name',
        'document',
        'photo_path',
        'apartment_id',
        'condominium_id',
        'visit_date'
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}
