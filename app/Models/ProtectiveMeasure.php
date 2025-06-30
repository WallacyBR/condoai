<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes; // Adicionado

class ProtectiveMeasure extends Model
{
    use HasFactory, SoftDeletes; // Adicionado SoftDeletes

    protected $fillable = [
        'protected_resident_id', // Alterado de morador_protegido_id
        'aggressor_visitor_id',  // Alterado de visitante_agressor_id
        'process_number',        // Alterado de numero_processo
        'measure_description',   // Alterado de descricao_medida
        'document_url',
        'validity_start_date',   // Alterado de data_inicio_validade
        'validity_end_date',     // Alterado de data_fim_validade
        'is_active',             // Alterado de ativa
        'condominium_id',
    ];

    protected $casts = [
        'validity_start_date' => 'date',
        'validity_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include protective measures of a given condominium.
     */
    public function scopeCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function protectedResident(): BelongsTo // Alterado de moradorProtegido
    {
        return $this->belongsTo(User::class, 'protected_resident_id'); // Alterado para protected_resident_id
    }

    public function aggressorVisitor(): BelongsTo // Alterado de visitanteAgressor
    {
        return $this->belongsTo(Visitor::class, 'aggressor_visitor_id'); // Alterado para aggressor_visitor_id e Visitor
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}
