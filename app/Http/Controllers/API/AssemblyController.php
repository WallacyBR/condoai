<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Assembly;
use App\Models\AssemblyAgenda;
use App\Models\AssemblyVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @group Assembleias Virtuais
 *
 * APIs para convocação, deliberação, votação digital e atas de assembleias condominiais.
 */
class AssemblyController extends Controller
{
    use ResolvesTenantCondominium;

    /**
     * Listar assembleias do condomínio autenticado.
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $query = Assembly::where('condominium_id', $condoId)
            ->with(['creator:id,name', 'agendas' => function ($q) {
                $q->orderBy('order');
            }])
            ->withCount('agendas');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $assemblies = $query->orderByDesc('first_call_at')->paginate($request->input('per_page', 15));

        return response()->json($assemblies);
    }

    /**
     * Criar uma nova assembleia com pautas iniciais.
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:ordinaria,extraordinaria',
            'modality' => 'required|string|in:presencial,virtual,hibrida',
            'first_call_at' => 'required|date',
            'second_call_at' => 'nullable|date|after_or_equal:first_call_at',
            'meeting_link' => 'nullable|url|max:500',
            'location' => 'nullable|string|max:255',
            'quorum_minimum' => 'nullable|numeric|min:0|max:100',
            'agendas' => 'nullable|array',
            'agendas.*.title' => 'required|string|max:255',
            'agendas.*.description' => 'nullable|string',
            'agendas.*.voting_type' => 'required|string|in:sim_nao_abstencao,multipla_escolha,eleicao',
            'agendas.*.options' => 'nullable|array',
            'agendas.*.weight_type' => 'nullable|string|in:unitario,fracao_ideal',
        ]);

        $assembly = DB::transaction(function () use ($validated, $condoId) {
            $assembly = Assembly::create([
                'condominium_id' => $condoId,
                'created_by' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'modality' => $validated['modality'],
                'status' => 'agendada',
                'first_call_at' => $validated['first_call_at'],
                'second_call_at' => $validated['second_call_at'] ?? null,
                'meeting_link' => $validated['meeting_link'] ?? null,
                'location' => $validated['location'] ?? null,
                'quorum_minimum' => $validated['quorum_minimum'] ?? null,
            ]);

            if (! empty($validated['agendas'])) {
                foreach ($validated['agendas'] as $index => $agendaData) {
                    $options = $agendaData['options'] ?? null;
                    if ($agendaData['voting_type'] === 'sim_nao_abstencao' && empty($options)) {
                        $options = ['A favor', 'Contra', 'Abstenção'];
                    }

                    $assembly->agendas()->create([
                        'title' => $agendaData['title'],
                        'description' => $agendaData['description'] ?? null,
                        'order' => $index + 1,
                        'voting_type' => $agendaData['voting_type'],
                        'options' => $options,
                        'status' => 'pendente',
                        'weight_type' => $agendaData['weight_type'] ?? 'unitario',
                    ]);
                }
            }

            return $assembly;
        });

        return response()->json($assembly->load('agendas'), 201);
    }

    /**
     * Exibir detalhes da assembleia, pautas e resultados.
     *
     * @authenticated
     */
    public function show(Request $request, Assembly $assembly)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId, 404);

        $assembly->load(['creator:id,name', 'agendas.votes']);

        // Obter apartamentos vinculados ao usuário autenticado para verificar se já votou
        $userApartmentIds = DB::table('apartment_resident')
            ->join('residents', 'residents.id', '=', 'apartment_resident.resident_id')
            ->where('residents.user_id', Auth::id())
            ->where('apartment_resident.active', true)
            ->pluck('apartment_resident.apartment_id')
            ->toArray();

        // Se não encontrou por resident_id, verifica se o usuário é proprietário/inquilino direto
        if (empty($userApartmentIds)) {
            $userApartmentIds = Apartment::where('condominium_id', $condoId)
                ->where(function ($q) {
                    $q->where('owner_id', Auth::id())
                        ->orWhere('tenant_id', Auth::id());
                })
                ->pluck('id')
                ->toArray();
        }

        $agendasData = $assembly->agendas->map(function ($agenda) use ($userApartmentIds) {
            $tally = $agenda->tallyResults();
            $userHasVoted = false;
            $userVote = null;

            if (! empty($userApartmentIds)) {
                $vote = $agenda->votes()->whereIn('apartment_id', $userApartmentIds)->first();
                if ($vote) {
                    $userHasVoted = true;
                    $userVote = [
                        'chosen_option' => $vote->chosen_option,
                        'apartment_id' => $vote->apartment_id,
                        'voted_at' => $vote->created_at,
                    ];
                }
            }

            return [
                'id' => $agenda->id,
                'title' => $agenda->title,
                'description' => $agenda->description,
                'order' => $agenda->order,
                'voting_type' => $agenda->voting_type,
                'options' => $agenda->options,
                'status' => $agenda->status,
                'weight_type' => $agenda->weight_type,
                'tally' => $tally,
                'user_has_voted' => $userHasVoted,
                'user_vote' => $userVote,
            ];
        });

        $response = $assembly->toArray();
        $response['agendas'] = $agendasData;
        $response['user_apartment_ids'] = $userApartmentIds;

        return response()->json($response);
    }

    /**
     * Atualizar dados da assembleia.
     *
     * @authenticated
     */
    public function update(Request $request, Assembly $assembly)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId, 404);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|string|in:ordinaria,extraordinaria',
            'modality' => 'sometimes|string|in:presencial,virtual,hibrida',
            'status' => 'sometimes|string|in:agendada,em_andamento,encerrada,cancelada',
            'first_call_at' => 'sometimes|date',
            'second_call_at' => 'nullable|date',
            'meeting_link' => 'nullable|url|max:500',
            'location' => 'nullable|string|max:255',
            'quorum_minimum' => 'nullable|numeric|min:0|max:100',
            'minutes' => 'nullable|string',
        ]);

        $assembly->update($validated);

        return response()->json($assembly->fresh('agendas'));
    }

    /**
     * Excluir ou cancelar assembleia.
     *
     * @authenticated
     */
    public function destroy(Request $request, Assembly $assembly)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId, 404);

        $assembly->delete();

        return response()->json(['message' => 'Assembleia excluída com sucesso.']);
    }

    /**
     * Iniciar assembleia.
     *
     * @authenticated
     */
    public function start(Request $request, Assembly $assembly)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId, 404);

        $assembly->update(['status' => 'em_andamento']);

        return response()->json([
            'message' => 'Assembleia iniciada com sucesso.',
            'assembly' => $assembly->fresh(),
        ]);
    }

    /**
     * Encerrar assembleia e compilar ata preliminar.
     *
     * @authenticated
     */
    public function close(Request $request, Assembly $assembly)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId, 404);

        // Fechar todas as pautas em aberto
        $assembly->agendas()->where('status', 'aberta')->update(['status' => 'encerrada']);

        // Gerar ata preliminar automática
        $minutes = $this->buildPreliminaryMinutes($assembly);

        $assembly->update([
            'status' => 'encerrada',
            'minutes' => $assembly->minutes ?: $minutes,
        ]);

        return response()->json([
            'message' => 'Assembleia encerrada com sucesso.',
            'assembly' => $assembly->fresh('agendas'),
        ]);
    }

    /**
     * Adicionar uma nova pauta à assembleia.
     *
     * @authenticated
     */
    public function addAgenda(Request $request, Assembly $assembly)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'voting_type' => 'required|string|in:sim_nao_abstencao,multipla_escolha,eleicao',
            'options' => 'nullable|array',
            'weight_type' => 'nullable|string|in:unitario,fracao_ideal',
        ]);

        $options = $validated['options'] ?? null;
        if ($validated['voting_type'] === 'sim_nao_abstencao' && empty($options)) {
            $options = ['A favor', 'Contra', 'Abstenção'];
        }

        $order = ($assembly->agendas()->max('order') ?? 0) + 1;

        $agenda = $assembly->agendas()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'order' => $order,
            'voting_type' => $validated['voting_type'],
            'options' => $options,
            'status' => 'pendente',
            'weight_type' => $validated['weight_type'] ?? 'unitario',
        ]);

        return response()->json($agenda, 201);
    }

    /**
     * Abrir votação de uma pauta.
     *
     * @authenticated
     */
    public function openAgendaVoting(Request $request, Assembly $assembly, AssemblyAgenda $agenda)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId || $agenda->assembly_id !== $assembly->id, 404);

        $agenda->update(['status' => 'aberta']);

        return response()->json([
            'message' => 'Votação da pauta aberta com sucesso.',
            'agenda' => $agenda->fresh(),
        ]);
    }

    /**
     * Encerrar votação de uma pauta.
     *
     * @authenticated
     */
    public function closeAgendaVoting(Request $request, Assembly $assembly, AssemblyAgenda $agenda)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId || $agenda->assembly_id !== $assembly->id, 404);

        $agenda->update(['status' => 'encerrada']);

        return response()->json([
            'message' => 'Votação da pauta encerrada.',
            'agenda' => $agenda->fresh(),
            'tally' => $agenda->tallyResults(),
        ]);
    }

    /**
     * Registrar voto digital em uma pauta.
     *
     * @authenticated
     */
    public function vote(Request $request, Assembly $assembly, AssemblyAgenda $agenda)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId || $agenda->assembly_id !== $assembly->id, 404);

        if ($agenda->status !== 'aberta') {
            return response()->json(['message' => 'A votação desta pauta não está aberta.'], 422);
        }

        $validated = $request->validate([
            'apartment_id' => [
                'required',
                'integer',
                Rule::exists('apartments', 'id')->where('condominium_id', $condoId),
            ],
            'chosen_option' => 'required|string',
            'is_proxy' => 'nullable|boolean',
            'proxy_document' => 'nullable|string',
        ]);

        // Verificar opções válidas
        $validOptions = is_array($agenda->options) ? $agenda->options : ['A favor', 'Contra', 'Abstenção'];
        if (! in_array($validated['chosen_option'], $validOptions)) {
            return response()->json(['message' => 'Opção de voto inválida.'], 422);
        }

        // Verificar se a unidade já votou
        $existingVote = AssemblyVote::where('assembly_agenda_id', $agenda->id)
            ->where('apartment_id', $validated['apartment_id'])
            ->first();

        if ($existingVote) {
            return response()->json(['message' => 'Esta unidade já registrou seu voto nesta pauta.'], 422);
        }

        // Calcular peso (se fração ideal ou 1.0000)
        $weight = 1.0000;
        if ($agenda->weight_type === 'fracao_ideal') {
            $apt = Apartment::find($validated['apartment_id']);
            $weight = $apt && $apt->ideal_fraction ? (float) $apt->ideal_fraction : 1.0000;
        }

        $vote = AssemblyVote::create([
            'assembly_agenda_id' => $agenda->id,
            'apartment_id' => $validated['apartment_id'],
            'user_id' => Auth::id(),
            'chosen_option' => $validated['chosen_option'],
            'weight' => $weight,
            'is_proxy' => $request->boolean('is_proxy'),
            'proxy_document' => $validated['proxy_document'] ?? null,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Voto registrado com sucesso!',
            'vote' => $vote,
            'tally' => $agenda->tallyResults(),
        ], 201);
    }

    /**
     * Obter resultado apurado da pauta.
     *
     * @authenticated
     */
    public function results(Request $request, Assembly $assembly, AssemblyAgenda $agenda)
    {
        $condoId = $this->tenantCondominiumId($request);
        abort_if($assembly->condominium_id !== $condoId || $agenda->assembly_id !== $assembly->id, 404);

        return response()->json([
            'agenda_id' => $agenda->id,
            'title' => $agenda->title,
            'status' => $agenda->status,
            'tally' => $agenda->tallyResults(),
        ]);
    }

    /**
     * Gera minuta preliminar da ata consolidando dados e deliberações.
     */
    protected function buildPreliminaryMinutes(Assembly $assembly): string
    {
        $assembly->load(['agendas.votes.apartment', 'condominium']);
        $condoName = $assembly->condominium?->name ?? 'Condomínio';
        $dateStr = $assembly->first_call_at->format('d/m/Y');
        $timeStr = $assembly->first_call_at->format('H:i');
        $typeStr = ucfirst($assembly->type);
        $modalityStr = ucfirst($assembly->modality);

        $text = 'ATA DA ASSEMBLEIA GERAL '.strtoupper($typeStr).' DO '.strtoupper($condoName)."\n\n";
        $text .= "Aos {$dateStr}, às {$timeStr} horas, realizou-se a Assembleia Geral {$typeStr}, de modalidade {$modalityStr}.\n";
        $text .= "Objeto e Convocação: {$assembly->title}.\n";
        if ($assembly->description) {
            $text .= "Descrição: {$assembly->description}\n\n";
        }
        $text .= "DELIBERAÇÕES E RESULTADOS DAS PAUTAS:\n";

        foreach ($assembly->agendas as $agenda) {
            $tally = $agenda->tallyResults();
            $text .= "\nPAUTA #{$agenda->order}: {$agenda->title}\n";
            $text .= "Total de Votos: {$tally['total_votes']} (Peso Total: {$tally['total_weight']})\n";
            foreach ($tally['breakdown'] as $option => $data) {
                $text .= " - {$option}: {$data['count']} votos ({$data['percentage']}%)\n";
            }
        }

        $text .= "\nNada mais havendo a tratar, encerrou-se a presente assembleia, lavrando-se a presente ata para os devidos fins de direito.";

        return $text;
    }
}
