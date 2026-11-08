<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\User;
use App\Models\UserPasskey;
use App\Services\Security\AccessLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityAdminController extends Controller
{
    public function accessLogs(Request $request)
    {
        $this->authorizeSecurityAdmin();

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:32'],
            'method' => ['nullable', 'string', 'max:32'],
            'user_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $condominiumId = Auth::guard('api')->user()->condominium_id;

        $logs = AccessLog::with('user:id,name,email')
            ->where('condominium_id', $condominiumId)
            ->when($validated['start_date'] ?? null, fn ($query, $date) => $query->whereDate('occurred_at', '>=', $date))
            ->when($validated['end_date'] ?? null, fn ($query, $date) => $query->whereDate('occurred_at', '<=', $date))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['method'] ?? null, fn ($query, $method) => $query->where('method', $method))
            ->when($validated['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('email', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('occurred_at')
            ->paginate($validated['per_page'] ?? 30);

        return response()->json($logs);
    }

    public function userPasskeys(User $user)
    {
        $this->authorizeSecurityAdmin();
        $this->ensureSameCondominium($user);

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'passkeys' => $user->passkeys()->with('revokedBy:id,name,email')->latest()->get(),
        ]);
    }

    public function revokeUserPasskey(Request $request, User $user, UserPasskey $passkey, AccessLogService $logs)
    {
        $actor = $this->authorizeSecurityAdmin();
        $this->ensureSameCondominium($user);
        abort_if($passkey->user_id !== $user->id, 404, 'Passkey não encontrada para este usuário.');

        if (! $passkey->revoked_at) {
            $passkey->revoke($actor);
            $logs->record($request, 'passkey', 'success', $user, null, 'Passkey revogada por administrador', [
                'passkey_id' => $passkey->id,
                'actor_id' => $actor->id,
            ]);
        }

        return response()->noContent();
    }

    private function authorizeSecurityAdmin(): User
    {
        $user = Auth::guard('api')->user();
        abort_if(! $user || ! $user->hasPermissionTo('view_security'), 403, 'Acesso restrito ao módulo Segurança.');

        return $user;
    }

    private function ensureSameCondominium(User $user): void
    {
        abort_if($user->condominium_id !== Auth::guard('api')->user()->condominium_id, 404, 'Usuário não encontrado.');
    }
}
