<?php

namespace App\Http\Controllers\API;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartAccount::query();
        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }
        return $query->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => ['required', Rule::exists('condominiums', 'id')],
            'parent_id' => ['nullable', Rule::exists('chart_accounts', 'id')],
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(AccountType::values())],
            'account_number' => 'nullable|string|max:50',
        ]);

        return ChartAccount::create($validated);
    }

    public function show(ChatAccount $chartAccount)
    {
        return $chartAccount;
    }

    public function update(Request $request, ChartAccount $chartAccount)
    {
        $validated = $request->validate([
            'condominium_id' => ['sometimes', Rule::exists('condominiums', 'id')],
            'parent_id' => ['nullable', Rule::exists('chart_accounts', 'id')],
            'name' => 'string|max:255',
            'type' => [Rule::in(AccountType::values())],
            'account_number' => 'nullable|string|max:50',
        ]);

        $chartAccount->update($validated);

        return $chartAccount;
    }

    public function destroy(ChatAccount $chartAccount)
    {
        $chartAccount->delete();
        return response()->json(null, 204);
    }
}
