<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Finance\CnabGateway;
use App\Services\Finance\MercadoPagoGateway;
use App\Services\Finance\StripeGateway;
use Illuminate\Http\Request;

class FinanceWebhookController extends Controller
{
    public function stripe(Request $request, StripeGateway $gateway)
    {
        $gateway->handleWebhook($request);
        return response()->json(['ok' => true]);
    }

    public function mercadoPago(Request $request, MercadoPagoGateway $gateway)
    {
        $gateway->handleWebhook($request);
        return response()->json(['ok' => true]);
    }

    public function cnab(Request $request, CnabGateway $gateway)
    {
        $gateway->handleWebhook($request);
        return response()->json(['ok' => true]);
    }
}
