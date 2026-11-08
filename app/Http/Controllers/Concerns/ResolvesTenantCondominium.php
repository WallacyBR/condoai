<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ResolvesTenantCondominium
{
    protected function tenantCondominiumId(Request $request): int
    {
        $condominium = tenant();

        if ($condominium === null && $request->attributes->has('condominium')) {
            $condominium = $request->attributes->get('condominium');
        }

        if ($condominium === null) {
            throw new \InvalidArgumentException(
                'Não foi possível determinar o condomínio atual. Envie o header X-Condominium-Id.'
            );
        }

        return $condominium->id;
    }
}
