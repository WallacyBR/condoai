<?php

namespace App\Http\Middleware;

use App\Models\Condominium;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $base = env('TENANT_BASE_DOMAIN');

        \Log::info('TenantMiddleware::handle - Debug info', [
            'host' => $host,
            'base' => $base,
            'path' => $request->path(),
            'method' => $request->method()
        ]);

        $subdomain = null;
        $condo = null;
        $requestedCondominiumId = $request->query('condominium_id') ?? $request->input('condominium_id');

        \Log::info('TenantMiddleware::handle - Condominium ID requested by frontend', ['requested_condominium_id' => $requestedCondominiumId]);

        // Se está em localhost ou desenvolvimento, usar o primeiro condomínio
        if ($host === 'localhost' || $host === '127.0.0.1' || str_contains($host, '.test') || app()->environment('local')) {
            \Log::info('TenantMiddleware::handle - Ambiente local detectado, usando primeiro condomínio');
            $condo = Condominium::first();
        } else {
            // Lógica original para produção
            if ($base && str_ends_with($host, $base) && $host !== $base) {
                $subdomain = trim(str_replace('.'.$base, '', $host));
            } else {
                $parts = explode('.', $host);
                if (count($parts) > 1) {
                    $subdomain = $parts[0];
                }
            }

            \Log::info('TenantMiddleware::handle - Subdomain detected', [
                'subdomain' => $subdomain
            ]);

            if ($subdomain) {
                $condo = Condominium::where('domain', $subdomain)->first();
                if (!$condo) {
                    \Log::error('TenantMiddleware::handle - Condomínio não encontrado', [
                        'subdomain' => $subdomain
                    ]);
                    throw new NotFoundHttpException('Condomínio não encontrado.');
                }
            }
        }
        
        // Se não encontrou condomínio de forma alguma, usar o primeiro disponível
        if (!$condo) {
            \Log::warning('TenantMiddleware::handle - Nenhum condomínio encontrado, usando primeiro disponível');
            $condo = Condominium::first();
        }
        
        if ($condo) {
            app()->instance('currentCondominium', $condo);
            $request->attributes->set('condominium', $condo);
            
            \Log::info('TenantMiddleware::handle - Condomínio definido', [
                'condominium_id' => $condo->id,
                'condominium_name' => $condo->name
            ]);
        } else {
            \Log::error('TenantMiddleware::handle - Nenhum condomínio disponível no sistema');
        }

        return $next($request);
    }
}
