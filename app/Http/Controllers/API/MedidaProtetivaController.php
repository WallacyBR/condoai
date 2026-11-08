<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MedidaProtetiva;
use App\Models\Visitante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MedidaProtetivaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        // Adicionar lógica de permissão aqui - quem pode listar todas as medidas?
        // Por enquanto, assume-se que o usuário autenticado (portaria/admin) pode listar do seu condomínio.

        $medidas = MedidaProtetiva::condominium($condominiumId)
            ->with(['moradorProtegido:id,name', 'visitanteAgressor:id,nome,documento'])
            ->orderBy('data_inicio_validade', 'desc')
            ->get();
        return response()->json($medidas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $validator = Validator::make($request->all(), [
            'morador_protegido_id' => 'required|exists:users,id', // Garantir que o user existe e pertence ao mesmo condomínio
            'visitante_agressor_id' => 'required|exists:visitantes,id', // Garantir que o visitante existe e pertence ao mesmo condomínio
            'numero_processo' => 'nullable|string|max:100',
            'descricao_medida' => 'required|string',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 'documento' será o campo do arquivo
            'data_inicio_validade' => 'required|date',
            'data_fim_validade' => 'nullable|date|after_or_equal:data_inicio_validade',
            'ativa' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['condominium_id'] = $condominiumId;

        // Validação adicional: morador e visitante devem pertencer ao mesmo condomínio
        $morador = User::where('id', $data['morador_protegido_id'])->where('condominium_id', $condominiumId)->first();
        if (!$morador) {
            return response()->json(['message' => 'Morador protegido não encontrado neste condomínio.'], 422);
        }
        $visitante = Visitante::where('id', $data['visitante_agressor_id'])->where('condominium_id', $condominiumId)->first();
        if (!$visitante) {
            return response()->json(['message' => 'Visitante agressor não encontrado neste condomínio.'], 422);
        }


        if ($request->hasFile('documento')) {
            $path = $request->file('documento')->store('medidas_protetivas_docs', 'public');
            $data['documento_url'] = Storage::url($path);
        }
        unset($data['documento']);


        $medida = MedidaProtetiva::create($data);

        return response()->json($medida, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $medida = MedidaProtetiva::condominium($condominiumId)
            ->with(['moradorProtegido:id,name', 'visitanteAgressor:id,nome,documento'])
            ->find($id);

        if (!$medida) {
            return response()->json(['message' => 'Medida Protetiva não encontrada.'], 404);
        }
        return response()->json($medida);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $medida = MedidaProtetiva::condominium($condominiumId)->find($id);

        if (!$medida) {
            return response()->json(['message' => 'Medida Protetiva não encontrada.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'morador_protegido_id' => 'sometimes|required|exists:users,id',
            'visitante_agressor_id' => 'sometimes|required|exists:visitantes,id',
            'numero_processo' => 'nullable|string|max:100',
            'descricao_medida' => 'sometimes|required|string',
            'documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'data_inicio_validade' => 'sometimes|required|date',
            'data_fim_validade' => 'nullable|date|after_or_equal:data_inicio_validade',
            'ativa' => 'sometimes|boolean',
            '_method' => 'sometimes|string|in:PUT,PATCH',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        // Validação adicional para condomínio se IDs forem alterados
        if (isset($data['morador_protegido_id'])) {
            $morador = User::where('id', $data['morador_protegido_id'])->where('condominium_id', $condominiumId)->first();
            if (!$morador) {
                return response()->json(['message' => 'Morador protegido não encontrado neste condomínio.'], 422);
            }
        }
        if (isset($data['visitante_agressor_id'])) {
            $visitante = Visitante::where('id', $data['visitante_agressor_id'])->where('condominium_id', $condominiumId)->first();
            if (!$visitante) {
                return response()->json(['message' => 'Visitante agressor não encontrado neste condomínio.'], 422);
            }
        }

        if ($request->hasFile('documento')) {
            if ($medida->documento_url) {
                $oldPath = str_replace(Storage::url(''), '', $medida->documento_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('documento')->store('medidas_protetivas_docs', 'public');
            $data['documento_url'] = Storage::url($path);
        } elseif ($request->input('documento_url') === null && $medida->documento_url) {
             $oldPath = str_replace(Storage::url(''), '', $medida->documento_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $data['documento_url'] = null;
        }
        unset($data['documento']);

        $medida->update($data);

        return response()->json($medida);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $medida = MedidaProtetiva::condominium($condominiumId)->find($id);

        if (!$medida) {
            return response()->json(['message' => 'Medida Protetiva não encontrada.'], 404);
        }

        if ($medida->documento_url) {
            $oldPath = str_replace(Storage::url(''), '', $medida->documento_url);
             if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $medida->delete(); // Soft delete

        return response()->json(null, 204);
    }
}
