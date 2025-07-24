<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Notifications\ExpenseRequestApproved;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpenseRequest::query();
        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => ['required', Rule::exists('condominiums', 'id')],
            'requested_by' => ['required', Rule::exists('users', 'id')],
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        return ExpenseRequest::create($validated);
    }

    public function approve(ExpenseRequest $expenseRequest)
    {
        $expenseRequest->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => 'aprovado',
        ]);

        $expenseRequest->requester->notify(new ExpenseRequestApproved($expenseRequest));

        return response()->json(['message' => 'Solicitação aprovada']);
    }
}
