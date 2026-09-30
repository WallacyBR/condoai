<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Notifications\ExpenseRequestApproved;
use Illuminate\Http\Request;

class ExpenseRequestController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $this->authorize('viewAny', ExpenseRequest::class);

        return ExpenseRequest::query()
            ->orderByDesc('created_at')
            ->get();
    }

    public function store(Request $request)
    {
        $this->authorize('create', ExpenseRequest::class);

        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        unset($validated['condominium_id'], $validated['requested_by']);
        $validated['condominium_id'] = $condoId;
        $validated['requested_by'] = auth()->id();

        $expenseRequest = ExpenseRequest::create($validated);

        return response()->json($expenseRequest, 201);
    }

    public function show(ExpenseRequest $expenseRequest)
    {
        $this->authorize('view', $expenseRequest);

        return response()->json($expenseRequest);
    }

    public function update(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->authorize('update', $expenseRequest);

        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0.01',
            'due_date' => 'sometimes|date',
            'description' => 'nullable|string',
            'status' => 'sometimes|string',
        ]);

        unset($validated['condominium_id'], $validated['requested_by']);
        $expenseRequest->update($validated);

        return response()->json($expenseRequest);
    }

    public function destroy(ExpenseRequest $expenseRequest)
    {
        $this->authorize('delete', $expenseRequest);

        $expenseRequest->delete();

        return response()->json(null, 204);
    }

    public function approve(ExpenseRequest $expenseRequest)
    {
        $this->authorize('approve', $expenseRequest);

        $expenseRequest->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => 'aprovado',
        ]);

        $expenseRequest->requester->notify(new ExpenseRequestApproved($expenseRequest));

        return response()->json(['message' => 'Solicitação aprovada']);
    }
}
