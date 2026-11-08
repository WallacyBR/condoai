<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Concerns\ResolvesCurrentCondominium;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\ExpenseApprovalRule;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestApproval;
use App\Models\Payable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseRequestController extends Controller
{
    use ResolvesCurrentCondominium;
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $this->authorize('viewAny', ExpenseRequest::class);
        $condominium = $this->currentCondominium($request);

        return ExpenseRequest::with(['requester:id,name', 'approver:id,name', 'approvals.approver:id,name', 'chartAccount:id,name', 'payable:id,status'])
            ->where('condominium_id', $condominium->id)
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $this->authorize('create', ExpenseRequest::class);
        $condominium = $this->currentCondominium($request);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'chart_account_id' => ['nullable', 'integer'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ensureAccount($validated['chart_account_id'] ?? null, $condominium->id);

        unset($validated['condominium_id'], $validated['requested_by'], $validated['status']);

        $expenseRequest = (new ExpenseRequest)->forceFill([
            ...$validated,
            'title' => $validated['title'] ?? ($validated['description'] ?? 'Solicitação de Despesa'),
            'condominium_id' => $condominium->id,
            'requested_by' => $request->user()->id,
            'status' => 'draft',
        ]);
        $expenseRequest->save();

        return response()->json($expenseRequest, 201);
    }

    public function show(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->authorize('view', $expenseRequest);
        $this->ensureRequestTenant($request, $expenseRequest);

        return response()->json(
            $expenseRequest->load(['requester', 'approver', 'approvals.approver', 'attachments', 'chartAccount', 'payable'])
        );
    }

    public function update(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->authorize('update', $expenseRequest);
        $this->ensureRequestTenant($request, $expenseRequest);

        $validated = $request->validate([
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'due_date' => ['sometimes', 'date'],
            'chart_account_id' => ['nullable', 'integer'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ensureAccount($validated['chart_account_id'] ?? $expenseRequest->chart_account_id, $expenseRequest->condominium_id);

        unset(
            $validated['condominium_id'],
            $validated['requested_by'],
            $validated['status'],
            $validated['approved_by'],
            $validated['approved_at']
        );

        $expenseRequest->update($validated);

        return response()->json($expenseRequest->fresh());
    }

    public function destroy(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->authorize('delete', $expenseRequest);
        $this->ensureRequestTenant($request, $expenseRequest);

        $expenseRequest->delete();

        return response()->json(null, 204);
    }

    public function approve(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->authorize('approve', $expenseRequest);
        $this->ensureRequestTenant($request, $expenseRequest);

        $expenseRequest->forceFill([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => 'aprovado',
        ])->save();

        return response()->json(['message' => 'Solicitação aprovada', 'data' => $expenseRequest->fresh()]);
    }

    public function submit(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->ensureRequestTenant($request, $expenseRequest);
        abort_unless($expenseRequest->requested_by === $request->user()->id && in_array($expenseRequest->status, ['draft', 'pendente', 'rejected'], true), 422, 'A solicitação não está disponível para envio.');

        $rules = $this->rulesFor($expenseRequest);
        if ($rules->isEmpty()) {
            throw ValidationException::withMessages(['approval_rules' => 'Configure ao menos uma regra de aprovação para esta faixa de valor.']);
        }

        $expenseRequest->forceFill(['status' => 'in_approval', 'submitted_at' => now(), 'rejected_at' => null, 'rejection_reason' => null])->save();

        return $expenseRequest->fresh('approvals');
    }

    public function decide(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->ensureRequestTenant($request, $expenseRequest);
        abort_unless($expenseRequest->status === 'in_approval', 422, 'A solicitação não está em aprovação.');
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'justification' => ['required', 'string', 'max:2000'],
        ]);
        abort_if($expenseRequest->requested_by === $request->user()->id, 403, 'O solicitante não pode aprovar a própria solicitação.');

        return DB::transaction(function () use ($request, $expenseRequest, $validated) {
            $rules = $this->rulesFor($expenseRequest);
            $approvals = $expenseRequest->approvals()->get()->keyBy('expense_approval_rule_id');
            $nextOrder = $rules->filter(fn ($rule) => ! $approvals->has($rule->id))->min('approval_order');
            $rule = $rules->first(fn ($candidate) => $candidate->approval_order === $nextOrder && $this->userMatchesRule($request->user(), $candidate));
            abort_unless($rule, 403, 'Você não é o próximo aprovador configurado para esta solicitação.');

            ExpenseRequestApproval::create([
                'expense_request_id' => $expenseRequest->id,
                'expense_approval_rule_id' => $rule->id,
                'approver_id' => $request->user()->id,
                'approval_order' => $rule->approval_order,
                'decision' => $validated['decision'],
                'justification' => $validated['justification'],
                'decided_at' => now(),
            ]);

            if ($validated['decision'] === 'rejected') {
                $expenseRequest->forceFill(['status' => 'rejected', 'rejected_at' => now(), 'rejection_reason' => $validated['justification']])->save();
            } elseif ($rules->count() === $approvals->count() + 1) {
                $expenseRequest->forceFill(['status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()])->save();
            }

            return $expenseRequest->fresh(['approvals.approver', 'approver']);
        });
    }

    public function convertToPayable(Request $request, ExpenseRequest $expenseRequest)
    {
        $this->ensureRequestTenant($request, $expenseRequest);
        abort_unless(in_array($expenseRequest->status, ['approved', 'aprovado'], true) && ! $expenseRequest->payable_id, 422, 'A solicitação precisa estar aprovada e ainda não convertida.');
        $validated = $request->validate([
            'bank_id' => ['nullable', 'integer'],
            'chart_account_id' => ['nullable', 'integer'],
        ]);
        $accountId = $validated['chart_account_id'] ?? $expenseRequest->chart_account_id;
        $this->ensureAccount($accountId, $expenseRequest->condominium_id);
        if (! $accountId) {
            throw ValidationException::withMessages(['chart_account_id' => 'Selecione uma conta financeira para criar a despesa.']);
        }

        $payable = Payable::create([
            'condominium_id' => $expenseRequest->condominium_id,
            'chart_account_id' => $accountId,
            'bank_id' => $validated['bank_id'] ?? null,
            'expense_request_id' => $expenseRequest->id,
            'due_date' => $expenseRequest->due_date,
            'competence_date' => $expenseRequest->due_date,
            'amount' => $expenseRequest->amount,
            'counterpart_name' => $expenseRequest->title,
            'reference' => $expenseRequest->reference,
            'description' => $expenseRequest->description,
            'status' => 'pending',
        ]);

        $expenseRequest->forceFill(['status' => 'converted', 'payable_id' => $payable->id])->save();

        return response()->json($payable, 201);
    }

    private function rulesFor(ExpenseRequest $request)
    {
        return ExpenseApprovalRule::where('condominium_id', $request->condominium_id)->where('active', true)
            ->where('minimum_amount', '<=', $request->amount)
            ->where(fn ($query) => $query->whereNull('maximum_amount')->orWhere('maximum_amount', '>=', $request->amount))
            ->orderBy('approval_order')->get();
    }

    private function userMatchesRule($user, ExpenseApprovalRule $rule): bool
    {
        return ($rule->user_id && $rule->user_id === $user->id) || ($rule->profile_id && $rule->profile_id === $user->profile_id);
    }

    private function ensureRequestTenant(Request $request, ExpenseRequest $expenseRequest): void
    {
        abort_unless($expenseRequest->condominium_id === $this->currentCondominium($request)->id, 404);
    }

    private function ensureAccount(?int $accountId, int $condominiumId): void
    {
        if ($accountId && ! ChartAccount::where('condominium_id', $condominiumId)->whereKey($accountId)->exists()) {
            throw ValidationException::withMessages(['chart_account_id' => 'A conta não pertence ao condomínio atual.']);
        }
    }
}
