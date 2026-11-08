<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Services\BankStatementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankStatementController extends Controller
{
    public function upload(Request $request, BankStatementService $service)
    {
        $validated = $request->validate([
            'bank_id' => ['required', Rule::exists('banks', 'id')],
            'file' => 'required|file',
        ]);

        $bank = Bank::findOrFail($validated['bank_id']);
        $transactions = $service->processUpload($validated['file'], $bank);

        return response()->json($transactions, 201);
    }
}
