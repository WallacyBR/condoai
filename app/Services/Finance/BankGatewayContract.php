<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;

interface BankGatewayContract
{
    public function handleWebhook(Request $request): void;
}
