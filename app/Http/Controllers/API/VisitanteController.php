<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Condominium;

class VisitanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $condominiumId = $request->query('condominium_id');
        if (!$condominiumId) {
            $user = auth()->user();
            if (!$user || !$user->condominium_id) {
                return response()->json(['message' => 'Condomínio não especificado ou usuário não associado a um condomínio.'], 400);
            }
            $condominiumId = $user->condominium_id;
        } else {
            // Validação adicional para garantir que o usuário tem acesso a este condomínio pode ser necessária
        }

        $visitantes = Visitante::condominium($condominiumId)->with('veiculos')->orderBy('nome')->get();
        return response()->json($visitantes);
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
            'nome' => 'required|string|max:255',
            'documento' => 'required|string|max:100|unique:visitantes,documento,NULL,id,condominium_id,' . $condominiumId,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 'foto' será o campo do arquivo
            'telefone' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string',
            'bloqueado' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['condominium_id'] = $condominiumId;

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('visitantes_fotos', 'public');
            $data['foto_url'] = Storage::url($path);
        }

        unset($data['foto']); // Remover 'foto' do array de dados antes de criar o modelo

        $visitante = Visitante::create($data);

        return response()->json($visitante, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $condominiumId = $request->query('condominium_id');
         if (!$condominiumId) {
            $user = auth()->user();
            if (!$user || !$user->condominium_id) {
                return response()->json(['message' => 'Condomínio não especificado.'], 400);
            }
            $condominiumId = $user->condominium_id;
        }

        $visitante = Visitante::condominium($condominiumId)->with('veiculos')->find($id);

        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }
        return response()->json($visitante);
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

        $visitante = Visitante::condominium($condominiumId)->find($id);

        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'documento' => 'sometimes|required|string|max:100|unique:visitantes,documento,' . $visitante->id . ',id,condominium_id,' . $condominiumId,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'telefone' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string',
            'bloqueado' => 'sometimes|boolean',
            '_method' => 'sometimes|string|in:PUT,PATCH', // Para lidar com FormData em PUT/PATCH
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('foto')) {
            // Apagar foto antiga se existir
            if ($visitante->foto_url) {
                $oldPath = str_replace(Storage::url(''), '', $visitante->foto_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('foto')->store('visitantes_fotos', 'public');
            $data['foto_url'] = Storage::url($path);
        } elseif ($request->input('foto_url') === null && $visitante->foto_url) {
            // Se foto_url é explicitamente null e existia uma foto, remove
             $oldPath = str_replace(Storage::url(''), '', $visitante->foto_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $data['foto_url'] = null;
        }


        unset($data['foto']);

        $visitante->update($data);

        return response()->json($visitante);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = auth()->user();
        $condominiumId = $user->condominium_id;

        $visitante = Visitante::condominium($condominiumId)->find($id);

        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }

        // Opcional: verificar se o visitante tem solicitações de acesso pendentes ou ativas antes de excluir
        // ou se está em alguma medida protetiva ativa.
        // Por enquanto, vamos apenas deletar. SoftDeletes está habilitado no modelo.

        if ($visitante->foto_url) {
            $oldPath = str_replace(Storage::url(''), '', $visitante->foto_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $visitante->delete();

        return response()->json(null, 204);
    }

    /**
     * Search for a visitor by document.
     */
    public function searchByDocument(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou não associado a um condomínio.'], 403);
        }
        $condominiumId = $user->condominium_id;

        $validator = Validator::make($request->all(), [
            'documento' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $visitante = Visitante::condominium($condominiumId)
            ->where('documento', $request->documento)
            ->with('veiculos')
            ->first();

        if (!$visitante) {
            return response()->json(['message' => 'Visitante não encontrado.'], 404);
        }

        return response()->json($visitante);
    }
}
