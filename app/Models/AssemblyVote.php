<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'assembly_agenda_id',
        'apartment_id',
        'user_id',
        'chosen_option',
        'weight',
        'is_proxy',
        'proxy_document',
        'ip_address',
    ];

    protected $casts = [
        'weight' => 'decimal:4',
        'is_proxy' => 'boolean',
    ];

    public function agenda()
    {
        return $this->belongsTo(AssemblyAgenda::class, 'assembly_agenda_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
