<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Security\AccessLogService;
use App\Services\Security\WebauthnPasskeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PasskeyAuthController extends Controller
{
    public function options(Request $request, WebauthnPasskeyService $passkeys)
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email'],
        ]);

        return response()->json($passkeys->loginOptions($request, $validated['email'] ?? null));
    }

    public function verify(Request $request, WebauthnPasskeyService $passkeys, AccessLogService $logs)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'credential' => ['required', 'array'],
        ]);

        try {
            $user = $passkeys->verifyLogin($request, $validated['token'], $validated['credential']);
            $token = Auth::guard('api')->login($user);
            $logs->record($request, 'passkey', 'success', $user);

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'current_condominium' => $user->condominium ? [
                    'id' => $user->condominium->id,
                    'name' => $user->condominium->name,
                    'domain' => $user->condominium->domain,
                ] : null,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ],
            ]);
        } catch (Throwable $exception) {
            $logs->record($request, 'passkey', 'failed', null, null, $exception->getMessage());
            throw $exception;
        }
    }
}
