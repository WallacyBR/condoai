<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssemblyAgenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'assembly_id',
        'title',
        'description',
        'order',
        'voting_type',  // sim_nao_abstencao, multipla_escolha, eleicao
        'options',      // array json
        'status',       // pendente, aberta, encerrada
        'weight_type',  // unitario, fracao_ideal
    ];

    protected $casts = [
        'options' => 'array',
        'order' => 'integer',
    ];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function votes()
    {
        return $this->hasMany(AssemblyVote::class);
    }

    /**
     * Calcula o resultado da apuração dos votos da pauta
     */
    public function tallyResults(): array
    {
        $votes = $this->votes()->with('apartment')->get();
        $totalVotes = $votes->count();
        $totalWeight = $votes->sum('weight');

        $breakdown = [];
        $options = is_array($this->options) && count($this->options) > 0
            ? $this->options
            : ($this->voting_type === 'sim_nao_abstencao' ? ['A favor', 'Contra', 'Abstenção'] : []);

        foreach ($options as $opt) {
            $breakdown[$opt] = [
                'count' => 0,
                'weight' => 0.0,
                'percentage' => 0.0,
            ];
        }

        foreach ($votes as $vote) {
            $opt = $vote->chosen_option;
            if (! isset($breakdown[$opt])) {
                $breakdown[$opt] = ['count' => 0, 'weight' => 0.0, 'percentage' => 0.0];
            }
            $breakdown[$opt]['count'] += 1;
            $breakdown[$opt]['weight'] += (float) $vote->weight;
        }

        if ($totalWeight > 0) {
            foreach ($breakdown as $opt => $data) {
                $breakdown[$opt]['percentage'] = round(($data['weight'] / $totalWeight) * 100, 2);
            }
        }

        return [
            'total_votes' => $totalVotes,
            'total_weight' => round($totalWeight, 4),
            'breakdown' => $breakdown,
        ];
    }
}
