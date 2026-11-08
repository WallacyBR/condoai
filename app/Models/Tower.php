<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tower extends Model
{
    use HasFactory;

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
}
