<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Condominium;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait AuthorizesCondominiumAccess
{
    protected function ensureCondominiumMembership(Request $request, Condominium $condominium): ?JsonResponse
    {
        if (! $request->user()->belongsToCondominium($condominium->id)) {
            return response()->json([
                'message' => 'Acesso negado ao condomínio informado.',
            ], 403);
        }

        return null;
    }
}
