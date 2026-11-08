<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileAnalyticsDashboardController extends Controller
{
    /**
     * Retorna dados gerais do dashboard
     */
    public function getDashboardData()
    {
        return response()->json([
            'general_stats' => $this->getGeneralStats(),
            'users_by_profile' => $this->getUsersByProfile(),
            'modules_usage' => $this->getModulesUsage(),
            'privileges_distribution' => $this->getPrivilegesDistribution(),
            'scope_analysis' => $this->getScopeAnalysis()
        ]);
    }

    /**
     * Estatísticas gerais
     */
    private function getGeneralStats()
    {
        return [
            'total_profiles' => Profile::count(),
            'total_users' => User::count(),
            'total_modules' => Module::count(),
            'active_profiles' => Profile::has('users')->count(),
            'unused_profiles' => Profile::doesntHave('users')->count()
        ];
    }

    /**
     * Distribuição de usuários por perfil
     */
    private function getUsersByProfile()
    {
        return Profile::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get()
            ->map(function ($profile) {
                return [
                    'profile_name' => $profile->name,
                    'users_count' => $profile->users_count,
                    'percentage' => $profile->users_count > 0 ? 
                        round(($profile->users_count / User::count()) * 100, 1) : 0
                ];
            });
    }

    /**
     * Análise de uso dos módulos
     */
    private function getModulesUsage()
    {
        return Module::withCount('profiles')
            ->get()
            ->map(function ($module) {
                return [
                    'module_name' => $module->name,
                    'profiles_with_access' => $module->profiles_count,
                    'usage_percentage' => Profile::count() > 0 ?
                        round(($module->profiles_count / Profile::count()) * 100, 1) : 0
                ];
            })
            ->sortByDesc('profiles_with_access')
            ->values();
    }

    /**
     * Distribuição de privilégios
     */
    private function getPrivilegesDistribution()
    {
        $totalPermissions = DB::table('module_profile')->count();
        
        if ($totalPermissions === 0) {
            return [
                'can_view' => ['count' => 0, 'percentage' => 0],
                'can_create' => ['count' => 0, 'percentage' => 0],
                'can_update' => ['count' => 0, 'percentage' => 0],
                'can_delete' => ['count' => 0, 'percentage' => 0]
            ];
        }

        return [
            'can_view' => [
                'count' => DB::table('module_profile')->where('can_view', true)->count(),
                'percentage' => round((DB::table('module_profile')->where('can_view', true)->count() / $totalPermissions) * 100, 1)
            ],
            'can_create' => [
                'count' => DB::table('module_profile')->where('can_create', true)->count(),
                'percentage' => round((DB::table('module_profile')->where('can_create', true)->count() / $totalPermissions) * 100, 1)
            ],
            'can_update' => [
                'count' => DB::table('module_profile')->where('can_update', true)->count(),
                'percentage' => round((DB::table('module_profile')->where('can_update', true)->count() / $totalPermissions) * 100, 1)
            ],
            'can_delete' => [
                'count' => DB::table('module_profile')->where('can_delete', true)->count(),
                'percentage' => round((DB::table('module_profile')->where('can_delete', true)->count() / $totalPermissions) * 100, 1)
            ]
        ];
    }

    /**
     * Análise de escopos
     */
    private function getScopeAnalysis()
    {
        $scopeCounts = DB::table('module_profile')
            ->select('scope', DB::raw('COUNT(*) as count'))
            ->groupBy('scope')
            ->get();

        $total = $scopeCounts->sum('count');

        return $scopeCounts->map(function ($item) use ($total) {
            return [
                'scope' => $item->scope,
                'label' => $this->getScopeLabel($item->scope),
                'count' => $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 1) : 0
            ];
        });
    }

    /**
     * Relatório detalhado de um perfil específico
     */
    public function getProfileDetailedReport(Profile $profile)
    {
        $profile->load(['modules', 'users.condominium']);

        $moduleStats = $profile->modules->map(function ($module) {
            $permissions = [
                'can_view' => $module->pivot->can_view,
                'can_create' => $module->pivot->can_create,
                'can_update' => $module->pivot->can_update,
                'can_delete' => $module->pivot->can_delete,
            ];
            
            return [
                'module_name' => $module->name,
                'module_description' => $module->description,
                'permissions' => $permissions,
                'scope' => $module->pivot->scope,
                'permission_count' => collect($permissions)->filter()->count(),
                'risk_level' => $this->getModuleRiskLevel($module, $permissions)
            ];
        });

        return response()->json([
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->name,
                'description' => $profile->description,
                'users_count' => $profile->users->count(),
                'modules_count' => $profile->modules->count(),
            ],
            'users' => $profile->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'condominium' => $user->condominium ? $user->condominium->name : null,
                    'created_at' => $user->created_at->format('d/m/Y H:i')
                ];
            }),
            'modules' => $moduleStats,
            'summary' => [
                'total_permissions' => $moduleStats->sum('permission_count'),
                'high_risk_modules' => $moduleStats->where('risk_level', 'high')->count(),
                'admin_level_access' => $moduleStats->where('scope', 'all')->count(),
                'complexity_score' => $this->calculateProfileComplexity($profile)
            ]
        ]);
    }

    // Helper methods
    private function getScopeLabel($scope)
    {
        return [
            'own' => 'Próprios registros',
            'condominium' => 'Registros do condomínio',
            'all' => 'Todos os registros'
        ][$scope] ?? $scope;
    }

    private function getModuleRiskLevel($module, $permissions)
    {
        $criticalModules = ['Usuários', 'Configurações', 'Condomínios'];
        
        if (in_array($module->name, $criticalModules) && $permissions['can_delete']) {
            return 'high';
        }
        
        if (collect($permissions)->filter()->count() >= 3) {
            return 'medium';
        }
        
        return 'low';
    }

    private function calculateProfileComplexity($profile)
    {
        $moduleCount = $profile->modules->count();
        $totalPermissions = $profile->modules->sum(function ($module) {
            return collect([
                $module->pivot->can_view,
                $module->pivot->can_create,
                $module->pivot->can_update,
                $module->pivot->can_delete,
            ])->filter()->count();
        });
        
        return ($moduleCount * 2) + $totalPermissions;
    }
} 