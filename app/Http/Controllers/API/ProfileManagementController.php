<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileManagementController extends Controller
{
    /**
     * Retorna dados completos para a interface de gerenciamento
     */
    public function getManagementData()
    {
        $profiles = Profile::with(['modules' => function($query) {
            $query->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'scope']);
        }])->withCount('users')->get();

        $modules = Module::all();

        $scopeOptions = [
            'own' => 'Próprios registros',
            'condominium' => 'Registros do condomínio',
            'all' => 'Todos os registros'
        ];

        return response()->json([
            'profiles' => $profiles,
            'modules' => $modules,
            'scope_options' => $scopeOptions,
            'protected_profiles' => ['SysOp'] // Perfis que não podem ser excluídos
        ]);
    }

    /**
     * Cria um novo perfil
     */
    public function createProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:profiles',
            'description' => 'nullable|string|max:500',
            'modules' => 'array',
            'modules.*.module_id' => 'exists:modules,id',
            'modules.*.can_view' => 'boolean',
            'modules.*.can_create' => 'boolean',
            'modules.*.can_update' => 'boolean',
            'modules.*.can_delete' => 'boolean',
            'modules.*.scope' => 'in:own,condominium,all',
        ]);

        DB::beginTransaction();
        try {
            $profile = Profile::create([
                'name' => $validated['name'],
                'description' => $validated['description']
            ]);

            if (isset($validated['modules'])) {
                $this->syncProfileModules($profile, $validated['modules']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Perfil criado com sucesso!',
                'profile' => $profile->load(['modules' => function($query) {
                    $query->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'scope']);
                }])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao criar perfil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza um perfil existente
     */
    public function updateProfile(Request $request, Profile $profile)
    {
        // Protege perfils críticos
        if ($profile->name === 'SysOp' && $request->user()->profile->name !== 'SysOp') {
            return response()->json(['message' => 'Não é possível alterar o perfil SysOp'], 403);
        }

        $validated = $request->validate([
            'name' => ['string', 'max:255', Rule::unique('profiles')->ignore($profile->id)],
            'description' => 'nullable|string|max:500',
            'modules' => 'array',
            'modules.*.module_id' => 'exists:modules,id',
            'modules.*.can_view' => 'boolean',
            'modules.*.can_create' => 'boolean',
            'modules.*.can_update' => 'boolean',
            'modules.*.can_delete' => 'boolean',
            'modules.*.scope' => 'in:own,condominium,all',
        ]);

        DB::beginTransaction();
        try {
            if (isset($validated['name']) || isset($validated['description'])) {
                $profile->update([
                    'name' => $validated['name'] ?? $profile->name,
                    'description' => $validated['description'] ?? $profile->description
                ]);
            }

            if (isset($validated['modules'])) {
                $this->syncProfileModules($profile, $validated['modules']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Perfil atualizado com sucesso!',
                'profile' => $profile->load(['modules' => function($query) {
                    $query->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'scope']);
                }])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao atualizar perfil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exclui um perfil
     */
    public function deleteProfile(Profile $profile)
    {
        // Protege perfis críticos
        if (in_array($profile->name, ['SysOp'])) {
            return response()->json(['message' => 'Este perfil não pode ser excluído'], 403);
        }

        // Verifica se há usuários usando este perfil
        if ($profile->users()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir este perfil pois existem usuários vinculados a ele'
            ], 409);
        }

        $profile->delete();

        return response()->json(['message' => 'Perfil excluído com sucesso!']);
    }

    /**
     * Duplica um perfil existente
     */
    public function duplicateProfile(Profile $profile, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:profiles',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $newProfile = Profile::create([
                'name' => $validated['name'],
                'description' => $validated['description']
            ]);

            // Copia todos os módulos e privilégios do perfil original
            $originalModules = $profile->modules()->withPivot([
                'can_view', 'can_create', 'can_update', 'can_delete', 'scope'
            ])->get();

            $syncData = [];
            foreach ($originalModules as $module) {
                $syncData[$module->id] = [
                    'can_view' => $module->pivot->can_view,
                    'can_create' => $module->pivot->can_create,
                    'can_update' => $module->pivot->can_update,
                    'can_delete' => $module->pivot->can_delete,
                    'scope' => $module->pivot->scope,
                ];
            }

            $newProfile->modules()->sync($syncData);

            DB::commit();

            return response()->json([
                'message' => 'Perfil duplicado com sucesso!',
                'profile' => $newProfile->load(['modules' => function($query) {
                    $query->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'scope']);
                }])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao duplicar perfil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Retorna usuários de um perfil específico
     */
    public function getProfileUsers(Profile $profile)
    {
        $users = $profile->users()
            ->with(['condominium:id,name'])
            ->select(['id', 'name', 'email', 'condominium_id', 'created_at'])
            ->get();

        return response()->json($users);
    }

    /**
     * Sincroniza módulos de um perfil
     */
    private function syncProfileModules(Profile $profile, array $modules)
    {
        $syncData = [];
        
        foreach ($modules as $moduleData) {
            $syncData[$moduleData['module_id']] = [
                'can_view' => $moduleData['can_view'] ?? false,
                'can_create' => $moduleData['can_create'] ?? false,
                'can_update' => $moduleData['can_update'] ?? false,
                'can_delete' => $moduleData['can_delete'] ?? false,
                'scope' => $moduleData['scope'] ?? 'own',
            ];
        }

        $profile->modules()->sync($syncData);
    }

    /**
     * Gera relatório de privilégios
     */
    public function getPrivilegesReport()
    {
        $profiles = Profile::with(['modules' => function($query) {
            $query->withPivot(['can_view', 'can_create', 'can_update', 'can_delete', 'scope']);
        }, 'users:id,name,profile_id'])->get();

        $report = $profiles->map(function ($profile) {
            return [
                'profile_name' => $profile->name,
                'profile_description' => $profile->description,
                'users_count' => $profile->users->count(),
                'modules_count' => $profile->modules->count(),
                'modules' => $profile->modules->map(function ($module) {
                    return [
                        'module_name' => $module->name,
                        'can_view' => $module->pivot->can_view,
                        'can_create' => $module->pivot->can_create,
                        'can_update' => $module->pivot->can_update,
                        'can_delete' => $module->pivot->can_delete,
                        'scope' => $module->pivot->scope,
                    ];
                })
            ];
        });

        return response()->json($report);
    }
} 