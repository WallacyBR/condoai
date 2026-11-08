<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;

class CnabGateway implements BankGatewayContract
{
    public function handleWebhook(Request $request): void
    {
        // TODO: implementar processamento para concilia\u00e7\u00e3o via CNAB
    }
}
