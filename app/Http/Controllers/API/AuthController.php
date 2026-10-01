<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Models\User;
use App\Services\Security\AccessLogService;
use App\Services\UserApartmentLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ResolvesTenantCondominium;

    public function __construct(
        private readonly UserApartmentLinkService $apartmentLinkService
    ) {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        if (app()->bound('currentCondominium')) {
            $data['condominium_id'] = app('currentCondominium')->id;
        }

        $user = User::create($data);

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ]);
    }

    public function login(Request $request, AccessLogService $accessLogs)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $attemptedUser = User::where('email', $validated['email'])->first();

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            $accessLogs->record($request, 'password', 'failed', $attemptedUser, $validated['email'], 'Credenciais inválidas');

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::guard('api')->user()->load(['profile.modules', 'condominiums', 'condominium']);
        $accessLogs->record($request, 'password', 'success', $user);

        return response()->json([
            'status' => 'success',
            'user' => [
                ...$user->toArray(),
                'memberships' => $user->condominiums,
            ],
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ]);
    }

    public function logout(Request $request, AccessLogService $accessLogs)
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            $accessLogs->record($request, 'logout', 'success', $user);
        }

        Auth::guard('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        $user = Auth::guard('api')->user()->load(['profile.modules', 'condominium']);

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'current_condominium' => $user->condominium ? [
                'id' => $user->condominium->id,
                'name' => $user->condominium->name,
                'domain' => $user->condominium->domain,
            ] : null,
            'authorization' => [
                'token' => Auth::guard('api')->refresh(),
                'type' => 'bearer',
            ],
        ]);
    }

    public function me()
    {
        $user = Auth::guard('api')->user()->load(['profile.modules', 'condominiums', 'condominium']);

        return response()->json([
            ...$user->toArray(),
            'memberships' => $user->condominiums,
        ]);
    }

    /**
     * Retorna o condomínio atual definido pelo middleware
     */
    public function currentCondominium()
    {
        $condominium = tenant();

        if ($condominium === null) {
            return response()->json([
                'current_condominium' => null,
                'message' => 'Nenhum condomínio definido no contexto atual',
            ]);
        }

        return response()->json([
            'current_condominium' => [
                'id' => $condominium->id,
                'name' => $condominium->name,
                'domain' => $condominium->domain,
            ],
        ]);
    }

    /**
     * Lista unidades do usuário autenticado no tenant atual (contrato Flutter /my-apartments).
     *
     * @authenticated
     */
    public function myApartments(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);
        $user = Auth::guard('api')->user();

        $units = $this->apartmentLinkService->myApartmentsForUser($user, $condominiumId);

        return response()->json(['data' => $units]);
    }
}
