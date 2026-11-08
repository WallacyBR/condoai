<?php

namespace App\Http\Middleware;

use App\Models\Condominium;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerValue = $request->header('X-Condominium-Id');

        if ($headerValue === null || $headerValue === '') {
            return response()->json([
                'message' => 'Header X-Condominium-Id é obrigatório.',
            ], 400);
        }

        if (! is_numeric($headerValue) || (int) $headerValue <= 0) {
            return response()->json([
                'message' => 'Header X-Condominium-Id inválido.',
            ], 400);
        }

        $condominiumId = (int) $headerValue;
        $condominium = Condominium::query()->find($condominiumId);

        if ($condominium === null) {
            return response()->json([
                'message' => 'Condomínio não encontrado.',
            ], 404);
        }

        $user = auth('api')->user();

        if ($user !== null && ! $user->belongsToCondominium($condominiumId)) {
            return response()->json([
                'message' => 'Acesso negado ao condomínio informado.',
            ], 403);
        }

        TenantContext::set($condominium);
        $request->attributes->set('condominium', $condominium);

        return $next($request);
    }
}
