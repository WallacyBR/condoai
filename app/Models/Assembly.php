<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assembly extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'condominium_id',
        'created_by',
        'title',
        'description',
        'type',           // ordinaria, extraordinaria
        'modality',       // presencial, virtual, hibrida
        'status',         // agendada, em_andamento, encerrada, cancelada
        'first_call_at',
        'second_call_at',
        'meeting_link',
        'location',
        'minutes',
        'quorum_minimum',
    ];

    protected $casts = [
        'first_call_at' => 'datetime',
        'second_call_at' => 'datetime',
        'quorum_minimum' => 'decimal:2',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agendas()
    {
        return $this->hasMany(AssemblyAgenda::class)->orderBy('order');
    }
}
