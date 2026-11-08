<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes; // Adicionado

class AccessRequest extends Model
{
    use HasFactory, SoftDeletes; // Adicionado SoftDeletes

    protected $fillable = [
        'visitor_id',
        'visitor_vehicle_id', // Alterado de veiculo_visitante_id
        'resident_id',        // Alterado de morador_destino_id
        'request_timestamp',  // Alterado de data_hora_solicitacao
        'status',             // Alterado de status_solicitacao
        'resident_observation', // Alterado de observacao_morador
        'gatehouse_observation',// Alterado de observacao_portaria
        'resident_response_timestamp', // Alterado de data_hora_resposta_morador
        'actual_entry_timestamp',    // Alterado de data_hora_entrada_efetiva
        'actual_exit_timestamp',     // Alterado de data_hora_saida_efetiva
        'parking_spot_id',    // Alterado de vaga_utilizada_id
        'condominium_id',
    ];

    protected $casts = [
        'request_timestamp' => 'datetime',
        'resident_response_timestamp' => 'datetime',
        'actual_entry_timestamp' => 'datetime',
        'actual_exit_timestamp' => 'datetime',
    ];

    /**
     * Scope a query to only include access requests of a given condominium.
     */
    public function scopeCondominium($query, $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class); // Alterado para Visitor
    }

    public function visitorVehicle(): BelongsTo
    {
        return $this->belongsTo(VisitorVehicle::class); // Alterado para VisitorVehicle
    }

    public function resident(): BelongsTo // Alterado de moradorDestino
    {
        return $this->belongsTo(User::class, 'resident_id'); // Alterado para resident_id
    }

    public function parkingSpot(): BelongsTo // Alterado de vagaUtilizada
    {
        return $this->belongsTo(ParkingSpot::class, 'parking_spot_id'); // Alterado para parking_spot_id
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}
