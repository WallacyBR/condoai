<?php

namespace Tests\Concerns;

use App\Models\Condominium;
use App\Models\User;

trait InteractsWithTenant
{
    protected function withTenantHeader(Condominium $condominium): static
    {
        return $this->withHeader('X-Condominium-Id', (string) $condominium->id);
    }

    protected function actingAsTenant(User $user, Condominium $condominium, string $guard = 'api'): static
    {
        return $this->actingAs($user, $guard)->withTenantHeader($condominium);
    }
}
