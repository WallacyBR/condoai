<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $query = Bank::query();
        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }
        return $query->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => ['required', Rule::exists('condominiums', 'id')],
            'name' => 'required|string|max:255',
            'agency' => 'nullable|string|max:50',
            'account_number' => 'required|string|max:50',
            'balance' => 'numeric|min:0',
        ]);

        return Bank::create($validated);
    }

    public function show(Bank $bank)
    {
        return $bank;
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'condominium_id' => ['sometimes', Rule::exists('condominiums', 'id')],
            'name' => 'string|max:255',
            'agency' => 'nullable|string|max:50',
            'account_number' => 'string|max:50',
            'balance' => 'numeric|min:0',
        ]);

        $bank->update($validated);

        return $bank;
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return response()->json(null, 204);
    }
}
