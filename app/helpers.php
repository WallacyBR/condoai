<?php

use App\Models\Condominium;
use App\Support\TenantContext;

if (! function_exists('tenant')) {
    function tenant(): ?Condominium
    {
        if (! TenantContext::isSet()) {
            return null;
        }

        return TenantContext::get();
    }
}
