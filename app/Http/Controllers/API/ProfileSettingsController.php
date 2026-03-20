<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->profile?->name,
            'notifications' => [
                'email' => (bool) $user->notification_email,
                'push' => (bool) $user->notification_push,
                'sms' => (bool) $user->notification_sms,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return $this->show($request);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(6)],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'A senha atual informada é inválida.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'message' => 'Senha atualizada com sucesso.',
        ]);
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|boolean',
            'push' => 'required|boolean',
            'sms' => 'required|boolean',
        ]);

        $request->user()->update([
            'notification_email' => $validated['email'],
            'notification_push' => $validated['push'],
            'notification_sms' => $validated['sms'],
        ]);

        return response()->json([
            'message' => 'Preferências de notificação atualizadas com sucesso.',
            'notifications' => $validated,
        ]);
    }
}
