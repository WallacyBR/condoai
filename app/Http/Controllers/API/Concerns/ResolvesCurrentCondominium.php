<?php

namespace App\Http\Controllers\API\Concerns;

use App\Models\Condominium;
use Illuminate\Http\Request;

trait ResolvesCurrentCondominium
{
    protected function currentCondominium(Request $request): Condominium
    {
        if (function_exists('tenant') && tenant() !== null) {
            return tenant();
        }

        if (app()->bound('currentCondominium')) {
            return app('currentCondominium');
        }

        if ($request->attributes->has('condominium')) {
            return $request->attributes->get('condominium');
        }

        if ($request->user() && $request->user()->condominium) {
            return $request->user()->condominium;
        }

        throw new \InvalidArgumentException(
            'Não foi possível determinar o condomínio atual. Envie o header X-Condominium-Id.'
        );
    }
}
