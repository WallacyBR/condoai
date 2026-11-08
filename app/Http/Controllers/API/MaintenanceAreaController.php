<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @group Manutenção - Áreas
 *
 * APIs para gerenciar áreas de manutenção.
 */
class MaintenanceAreaController extends Controller
{
    /**
     * Listar todas as áreas de manutenção.
     *
     * Retorna uma lista paginada das áreas de manutenção do condomínio autenticado.
     *
     * @authenticated
     * @queryParam page int A página a ser retornada. Example: 1
     * @queryParam per_page int Número de itens por página. Example: 15
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenanceareas.index.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function index(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        $perPage = $request->input('per_page', 15);
        $areas = MaintenanceArea::where('condominium_id', $currentCondominiumId)
                                ->orderBy('name', 'asc')
                                ->paginate($perPage);
        return response()->json($areas);
    }

    /**
     * Criar uma nova área de manutenção.
     *
     * @authenticated
     * @bodyParam name string required Nome da área de manutenção. Example: "Elétrica Geral"
     * @bodyParam description string Descrição da área. Example: "Todas as instalações elétricas do prédio A."
     *
     * @responseFile status=201 scenario="Criado com sucesso" storage/responses/maintenancearea.store.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function store(Request $request)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $area = MaintenanceArea::create([
            'name' => $request->name,
            'description' => $request->description,
            'condominium_id' => $currentCondominiumId,
        ]);

        return response()->json($area, 201);
    }

    /**
     * Exibir uma área de manutenção específica.
     *
     * @authenticated
     * @urlParam area int required O ID da área de manutenção. Example: 1
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/maintenancearea.show.json
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function show(Request $request, MaintenanceArea $area)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        // Policy check: Ensure the area belongs to the user's condominium
        if ($area->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a esta área.'], 403);
        }

        return response()->json($area);
    }

    /**
     * Atualizar uma área de manutenção.
     *
     * @authenticated
     * @urlParam area int required O ID da área de manutenção. Example: 1
     * @bodyParam name string Nome da área de manutenção. Example: "Elétrica Prédio B"
     * @bodyParam description string Descrição da área. Example: "Instalações elétricas atualizadas do prédio B."
     *
     * @responseFile status=200 scenario="Atualizado com sucesso" storage/responses/maintenancearea.update.json
     * @responseFile status=400 scenario="Dados inválidos" storage/responses/validation.error.json
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function update(Request $request, MaintenanceArea $area)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        // Policy check
        if ($area->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a esta área.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $area->update($request->only(['name', 'description']));

        return response()->json($area);
    }

    /**
     * Excluir uma área de manutenção.
     *
     * @authenticated
     * @urlParam area int required O ID da área de manutenção. Example: 1
     *
     * @response status=204 scenario="Excluído com sucesso"
     * @responseFile status=404 scenario="Não encontrado" storage/responses/notfound.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     * @responseFile status=403 scenario="Proibido" storage/responses/forbidden.json
     */
    public function destroy(Request $request, MaintenanceArea $area)
    {
        $condominiumId = Auth::user()->condominium_id;
        if (!$condominiumId && !$request->attributes->has('condominium')) {
            return response()->json(['message' => 'Condomínio não identificado para o usuário.'], 400);
        }
        $currentCondominiumId = $condominiumId ?? $request->attributes->get('condominium')->id;

        if ($area->condominium_id != $currentCondominiumId) {
            return response()->json(['message' => 'Acesso não autorizado a esta área.'], 403);
        }

        // Verificar se existem equipamentos associados antes de excluir
        if ($area->equipments()->exists()) {
            return response()->json(['message' => 'Não é possível excluir a área pois existem equipamentos associados.'], 400);
        }

        $area->delete();

        return response()->json(null, 204);
    }
}
