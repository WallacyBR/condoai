<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;

class MercadoPagoGateway implements BankGatewayContract
{
    public function handleWebhook(Request $request): void
    {
        // TODO: implementar processamento real do webhook do Mercado Pago
    }
}
