<?php

namespace App\Support;

use App\Models\Condominium;

/**
 * Restaura e limpa TenantContext em jobs/listeners de worker (ADR-002 E2).
 *
 * Workers long-lived não possuem request HTTP; o contexto deve ser setado por job
 * e completamente removido no finally para evitar vazamento cross-tenant.
 */
trait RestoresTenantContext
{
    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function withTenantContext(int $condominiumId, callable $callback): mixed
    {
        $condominium = Condominium::query()->findOrFail($condominiumId);
        TenantContext::set($condominium);

        try {
            return $callback();
        } finally {
            TenantContext::clear();
        }
    }
}
