<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Verificar a saúde da aplicação
     */
    public function check(): JsonResponse
    {
        try {
            // Verificar conexão com banco de dados
            DB::connection()->getPdo();

            return response()->json([
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'database' => 'connected',
                'version' => '1.0.0',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'database' => 'disconnected',
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}
