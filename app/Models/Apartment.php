<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'floor',
        'block_id',
        'owner_id',
        'tenant_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'floor' => 'integer'
    ];

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function owner()
    {
        return $this->belongsTo(Resident::class, 'owner_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Resident::class, 'tenant_id');
    }

    public function residents()
    {
        return $this->belongsToMany(Resident::class, 'apartment_resident')
            ->withPivot('type', 'active')
            ->withTimestamps();
    }

    public function allResidents()
    {
        return $this->residents()->wherePivot('type', 'resident');
    }
}
