<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Concerns\ResolvesCurrentCondominium;
use App\Http\Controllers\Controller;
use App\Models\ExpenseApprovalRule;
use App\Models\User;
use Illuminate\Http\Request;

class ExpenseApprovalRuleController extends Controller
{
    use ResolvesCurrentCondominium;

    public function index(Request $request)
    {
        return ExpenseApprovalRule::with(['profile:id,name', 'user:id,name'])->where('condominium_id', $this->currentCondominium($request)->id)->orderBy('approval_order')->get();
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $condominium = $this->currentCondominium($request);
        $this->ensureApprover($validated, $condominium->id);

        return response()->json(ExpenseApprovalRule::create([...$validated, 'condominium_id' => $condominium->id]), 201);
    }

    public function update(Request $request, ExpenseApprovalRule $expenseApprovalRule)
    {
        abort_unless($expenseApprovalRule->condominium_id === $this->currentCondominium($request)->id, 404);
        $validated = $this->validated($request, true);
        $this->ensureApprover([...$expenseApprovalRule->only(['profile_id', 'user_id']), ...$validated], $expenseApprovalRule->condominium_id);
        $expenseApprovalRule->update($validated);

        return $expenseApprovalRule->fresh();
    }

    public function destroy(Request $request, ExpenseApprovalRule $expenseApprovalRule)
    {
        abort_unless($expenseApprovalRule->condominium_id === $this->currentCondominium($request)->id, 404);
        $expenseApprovalRule->delete();

        return response()->noContent();
    }

    private function validated(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'profile_id' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'exists:profiles,id'],
            'user_id' => [$partial ? 'sometimes' : 'nullable', 'nullable', 'exists:users,id'],
            'minimum_amount' => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'gte:minimum_amount'],
            'approval_order' => [$partial ? 'sometimes' : 'required', 'integer', 'min:1'],
            'active' => ['sometimes', 'boolean'],
        ]);
    }

    private function ensureApprover(array $validated, int $condominiumId): void
    {
        abort_if(empty($validated['profile_id']) && empty($validated['user_id']), 422, 'Informe um perfil ou usuário aprovador.');

        if (! empty($validated['user_id'])) {
            abort_unless(
                User::whereKey($validated['user_id'])->where('condominium_id', $condominiumId)->exists(),
                422,
                'O usuário aprovador precisa pertencer ao condomínio atual.'
            );
        }
    }
}
