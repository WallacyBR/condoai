<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Gerenciamento de Usuários
 *
 * APIs para gerenciar usuários do sistema.
 */
class UserController extends Controller
{
    /**
     * Listar usuários.
     *
     * Retorna uma lista paginada de usuários. Pode ser filtrada por perfil e/ou condomínio.
     * Apenas usuários do mesmo condomínio do requisitante (ou todos para super admins) são retornados.
     *
     * @authenticated
     * @queryParam profile_id int ID do perfil para filtrar usuários. Example: 3
     * @queryParam condominium_id int ID do condomínio para filtrar usuários. Se não fornecido e o usuário autenticado tiver um condomínio, usa o do usuário. Example: 1
     * @queryParam per_page int Número de itens por página. Padrão: 15. Example: 10
     * @queryParam page int A página a ser retornada. Example: 1
     *
     * @responseFile status=200 scenario="Sucesso" storage/responses/users.index.json
     * @responseFile status=401 scenario="Não autenticado" storage/responses/unauthenticated.json
     */
    public function index(Request $request)
    {
        $query = User::with('profile');

        // Filtrar por condomínio - prioriza o condominium_id da requisição,
        // caso contrário, usa o do usuário autenticado, se disponível.
        $condominiumId = $request->input('condominium_id', auth()->user()->condominium_id ?? null);

        if ($condominiumId) {
            $query->where('condominium_id', $condominiumId);
        }
        // Se um superadmin não tem condominium_id, ele poderia ver todos,
        // mas para este contexto, vamos assumir que sempre há um condomínio.

        if ($request->has('profile_id')) {
            $query->where('profile_id', $request->input('profile_id'));
        }

        $perPage = $request->input('per_page', 15);

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'profile_id' => 'required|exists:profiles,id',
            'condominium_id' => 'required|exists:condominiums,id',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        return User::create($validated)->load('profile');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user->load('profile');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => ['email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'profile_id' => 'exists:profiles,id',
            'condominium_id' => 'exists:condominiums,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return $user->load('profile');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}
