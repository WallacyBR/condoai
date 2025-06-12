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

        $subdomain = null;
        if ($base && str_ends_with($host, $base) && $host !== $base) {
            $subdomain = trim(str_replace('.' . $base, '', $host));
        } else {
            $parts = explode('.', $host);
            if (count($parts) > 1) {
                $subdomain = $parts[0];
            }
        }

        if ($subdomain) {
            $condo = Condominium::where('domain', $subdomain)->first();
            if (!$condo) {
                throw new NotFoundHttpException('Condom\u00ednio n\u00e3o encontrado.');
            }
            app()->instance('currentCondominium', $condo);
            $request->attributes->set('condominium', $condo);
        }

        return $next($request);
    }
}
