<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BudgetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BudgetItemController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $budgetItems = BudgetItem::where('year', $request->year)->get();
        return response()->json($budgetItems);
    }

    public function store(Request $request)
    {
        $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $budgetItem = BudgetItem::create($request->all());
        return response()->json($budgetItem, 201);
    }

    public function show(BudgetItem $budgetItem)
    {
        return response()->json($budgetItem);
    }

    public function update(Request $request, BudgetItem $budgetItem)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $budgetItem->update($request->only('amount'));
        return response()->json($budgetItem);
    }

    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.chart_account_id' => 'required|exists:chart_accounts,id',
            'items.*.month' => 'required|integer|min:1|max:12',
            'items.*.year' => 'required|integer|min:2000|max:2100',
            'items.*.amount' => 'required|numeric|min:0',
            'condominium_id' => 'required|exists:condominiums,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $items = $request->input('items', []);
        $condominiumId = $request->input('condominium_id');

        foreach ($items as $itemData) {
            BudgetItem::updateOrCreate(
                [
                    'condominium_id' => $condominiumId,
                    'chart_account_id' => $itemData['chart_account_id'],
                    'month' => $itemData['month'],
                    'year' => $itemData['year'],
                ],
                ['amount' => $itemData['amount']]
            );
        }

        return response()->json(['message' => 'Orçamento atualizado com sucesso.']);
    }


    public function destroy(BudgetItem $budgetItem)
    {
        $budgetItem->delete();
        return response()->json(null, 204);
    }
}
