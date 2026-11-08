<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserPasskey;
use App\Services\Security\AccessLogService;
use App\Services\Security\WebauthnPasskeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityPasskeyController extends Controller
{
    public function index()
    {
        return Auth::guard('api')->user()
            ->passkeys()
            ->with('revokedBy:id,name,email')
            ->latest()
            ->get();
    }

    public function registrationOptions(Request $request, WebauthnPasskeyService $passkeys)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json($passkeys->registrationOptions(
            $request,
            Auth::guard('api')->user(),
            $validated['name'] ?? null
        ));
    }

    public function verifyRegistration(Request $request, WebauthnPasskeyService $passkeys, AccessLogService $logs)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:120'],
            'credential' => ['required', 'array'],
        ]);

        $passkey = $passkeys->verifyRegistration(
            $request,
            $validated['token'],
            $validated['credential'],
            $validated['name'] ?? null
        );

        $logs->record($request, 'passkey', 'success', Auth::guard('api')->user(), null, 'Passkey criada', [
            'passkey_id' => $passkey->id,
        ]);

        return response()->json($passkey, 201);
    }

    public function destroy(Request $request, UserPasskey $passkey, AccessLogService $logs)
    {
        $user = Auth::guard('api')->user();
        abort_if($passkey->user_id !== $user->id, 403, 'Passkey não pertence ao usuário autenticado.');

        if (! $passkey->revoked_at) {
            $passkey->revoke($user);
            $logs->record($request, 'passkey', 'success', $user, null, 'Passkey revogada pelo usuário', [
                'passkey_id' => $passkey->id,
            ]);
        }

        return response()->noContent();
    }
}
