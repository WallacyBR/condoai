<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VeiculoVisitante;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VeiculoVisitanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $visitanteId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitante = Visitante::condominium($condominiumId)->find($visitanteId);
        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }

        return response()->json($visitante->veiculos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $visitanteId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitante = Visitante::condominium($condominiumId)->find($visitanteId);
        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'placa' => 'required|string|max:15|unique:veiculos_visitantes,placa,NULL,id,condominium_id,' . $condominiumId,
            'modelo' => 'required|string|max:100',
            'cor' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['visitante_id'] = $visitante->id;
        $data['condominium_id'] = $condominiumId;

        $veiculo = VeiculoVisitante::create($data);

        return response()->json($veiculo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $visitanteId, string $veiculoId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitante = Visitante::condominium($condominiumId)->find($visitanteId);
        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }

        $veiculo = $visitante->veiculos()->where('id', $veiculoId)->where('condominium_id', $condominiumId)->first();
        if (!$veiculo) {
            return response()->json(['message' => 'Veículo do visitante não encontrado.'], 404);
        }

        return response()->json($veiculo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $visitanteId, string $veiculoId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitante = Visitante::condominium($condominiumId)->find($visitanteId);
        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }

        $veiculo = $visitante->veiculos()->where('id', $veiculoId)->where('condominium_id', $condominiumId)->first();
        if (!$veiculo) {
            return response()->json(['message' => 'Veículo do visitante não encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'placa' => 'sometimes|required|string|max:15|unique:veiculos_visitantes,placa,' . $veiculo->id . ',id,condominium_id,' . $condominiumId,
            'modelo' => 'sometimes|required|string|max:100',
            'cor' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $veiculo->update($validator->validated());

        return response()->json($veiculo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $visitanteId, string $veiculoId)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $visitante = Visitante::condominium($condominiumId)->find($visitanteId);
        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }

        $veiculo = $visitante->veiculos()->where('id', $veiculoId)->where('condominium_id', $condominiumId)->first();
        if (!$veiculo) {
            return response()->json(['message' => 'Veículo do visitante não encontrado.'], 404);
        }

        // Adicionar verificação se o veículo está em alguma solicitação de acesso ativa?
        // Por enquanto, apenas deleta. SoftDeletes está habilitado.
        $veiculo->delete();

        return response()->json(null, 204);
    }
}
