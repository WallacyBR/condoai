<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\Concerns\ResolvesCurrentCondominium;
use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\ProcurementAward;
use App\Models\ProcurementRequest;
use App\Models\SupplierQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProcurementController extends Controller
{
    use ResolvesCurrentCondominium;

    public function index(Request $request)
    {
        return ProcurementRequest::withCount('invitations')->where('condominium_id', $this->currentCondominium($request)->id)->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'response_deadline' => ['required', 'date', 'after:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.specification' => ['nullable', 'string', 'max:2000'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);
        $condominium = $this->currentCondominium($request);

        return DB::transaction(function () use ($validated, $condominium, $request) {
            $procurement = ProcurementRequest::create([
                'condominium_id' => $condominium->id,
                'created_by' => $request->user()->id,
                'code' => 'COT-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'response_deadline' => $validated['response_deadline'],
            ]);
            foreach ($validated['items'] as $position => $item) {
                $procurement->items()->create([...$item, 'position' => $position]);
            }

            return response()->json($procurement->load('items'), 201);
        });
    }

    public function show(Request $request, ProcurementRequest $procurementRequest)
    {
        $this->ensureTenant($request, $procurementRequest);
        $procurementRequest->load(['items', 'invitations.supplier', 'invitations.quote.items.procurementItem', 'invitations.quote.attachments', 'awards.quote', 'awards.item']);
        $procurementRequest->invitations->each(function ($invitation) {
            $invitation->makeVisible('token');
            $invitation->setAttribute('response_url', url('/cotacoes/responder/'.$invitation->token));
            $invitation->makeHidden('token');
            if ($invitation->quote) {
                $invitation->quote->setAttribute('total', $invitation->quote->total());
            }
        });

        return $procurementRequest;
    }

    public function publish(Request $request, ProcurementRequest $procurementRequest)
    {
        $this->ensureTenant($request, $procurementRequest);
        abort_unless(in_array($procurementRequest->status, ['draft', 'published'], true), 422, 'A cotação não pode ser publicada neste estado.');
        abort_if($procurementRequest->items()->count() === 0, 422, 'Inclua ao menos um item antes de publicar.');
        $procurementRequest->update(['status' => 'published', 'published_at' => now()]);

        return $procurementRequest->fresh();
    }

    public function invite(Request $request, ProcurementRequest $procurementRequest)
    {
        $this->ensureTenant($request, $procurementRequest);
        abort_unless($procurementRequest->status === 'published', 422, 'Publique a cotação antes de convidar fornecedores.');
        $validated = $request->validate(['supplier_name' => ['required', 'string', 'max:255'], 'supplier_email' => ['nullable', 'email', 'max:255'], 'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id']]);
        $invitation = $procurementRequest->invitations()->create([
            ...$validated,
            'token' => Str::random(64),
            'expires_at' => $procurementRequest->response_deadline->copy()->endOfDay(),
        ]);

        return response()->json([
            'id' => $invitation->id,
            'supplier_name' => $invitation->supplier_name,
            'expires_at' => $invitation->expires_at,
            'response_url' => url('/cotacoes/responder/'.$invitation->token),
        ], 201);
    }

    public function close(Request $request, ProcurementRequest $procurementRequest)
    {
        $this->ensureTenant($request, $procurementRequest);
        abort_unless($procurementRequest->status === 'published', 422, 'A cotação não está aberta para encerramento.');
        $validated = $request->validate([
            'criterion' => ['required', 'in:lowest_total,lowest_item,best_value,manual'],
            'justification' => ['nullable', 'string', 'max:3000'],
            'publish_result' => ['required', 'boolean'],
            'awards' => ['required', 'array', 'min:1'],
            'awards.*.supplier_quote_id' => ['required', 'integer'],
            'awards.*.procurement_item_id' => ['nullable', 'integer'],
            'awards.*.justification' => ['nullable', 'string', 'max:2000'],
        ]);
        if (in_array($validated['criterion'], ['best_value', 'manual'], true) && blank($validated['justification'])) {
            throw ValidationException::withMessages(['justification' => 'Este critério exige justificativa da escolha.']);
        }

        return DB::transaction(function () use ($validated, $procurementRequest, $request) {
            $submittedQuotes = SupplierQuote::with('items')
                ->where('status', 'submitted')
                ->whereHas('invitation', fn ($query) => $query->where('procurement_request_id', $procurementRequest->id))
                ->get()
                ->keyBy('id');
            $this->validateAwards($validated, $procurementRequest, $submittedQuotes);

            ProcurementAward::where('procurement_request_id', $procurementRequest->id)->delete();
            foreach ($validated['awards'] as $award) {
                $quote = $submittedQuotes->get($award['supplier_quote_id']);
                abort_unless($quote, 422, 'A proposta selecionada precisa estar enviada e pertencer a esta cotação.');
                if (! empty($award['procurement_item_id']) && ! $procurementRequest->items()->whereKey($award['procurement_item_id'])->exists()) {
                    throw ValidationException::withMessages(['awards' => 'Um item escolhido não pertence a esta cotação.']);
                }
                ProcurementAward::create([
                    'procurement_request_id' => $procurementRequest->id,
                    'procurement_item_id' => $award['procurement_item_id'] ?? null,
                    'supplier_quote_id' => $quote->id,
                    'selected_by' => $request->user()->id,
                    'criterion' => $validated['criterion'],
                    'justification' => $award['justification'] ?? ($validated['justification'] ?? null),
                ]);
            }
            $procurementRequest->update([
                'status' => 'closed',
                'selection_criterion' => $validated['criterion'],
                'selection_justification' => $validated['justification'] ?? null,
                'publish_result' => $validated['publish_result'],
                'closed_at' => now(),
            ]);

            return $procurementRequest->fresh('awards');
        });
    }

    public function createExpenseRequest(Request $request, ProcurementRequest $procurementRequest)
    {
        $this->ensureTenant($request, $procurementRequest);
        abort_unless($procurementRequest->status === 'closed', 422, 'Encerre a cotação antes de criar a solicitação.');
        $validated = $request->validate(['due_date' => ['required', 'date'], 'chart_account_id' => ['nullable', 'integer']]);
        $awards = $procurementRequest->awards()->with('quote.items')->get();
        $amount = $awards->sum(fn ($award) => $award->procurement_item_id
            ? (float) optional($award->quote->items->firstWhere('procurement_item_id', $award->procurement_item_id))->unit_price * (float) $award->item->quantity
            : $award->quote->total());
        $expense = ExpenseRequest::create([
            'condominium_id' => $procurementRequest->condominium_id,
            'requested_by' => $request->user()->id,
            'chart_account_id' => $validated['chart_account_id'] ?? null,
            'title' => 'Cotação '.$procurementRequest->code.' - '.$procurementRequest->title,
            'reference' => $procurementRequest->code,
            'amount' => $amount,
            'due_date' => $validated['due_date'],
            'description' => $procurementRequest->selection_justification,
            'status' => 'draft',
        ]);

        return response()->json($expense, 201);
    }

    private function ensureTenant(Request $request, ProcurementRequest $procurementRequest): void
    {
        abort_unless($procurementRequest->condominium_id === $this->currentCondominium($request)->id, 404);
    }

    private function validateAwards(array $validated, ProcurementRequest $procurementRequest, $submittedQuotes): void
    {
        if ($submittedQuotes->isEmpty()) {
            throw ValidationException::withMessages(['awards' => 'Não há propostas enviadas para encerrar esta cotação.']);
        }

        if ($validated['criterion'] === 'lowest_total') {
            if (count($validated['awards']) !== 1) {
                throw ValidationException::withMessages(['awards' => 'O critério de menor valor total exige uma única proposta.']);
            }

            $selected = $submittedQuotes->get($validated['awards'][0]['supplier_quote_id']);
            $lowestTotal = $submittedQuotes->min(fn (SupplierQuote $quote) => $quote->total());
            if (! $selected || $selected->total() > $lowestTotal) {
                throw ValidationException::withMessages(['awards' => 'A proposta escolhida não possui o menor valor total.']);
            }

            return;
        }

        if ($validated['criterion'] !== 'lowest_item') {
            return;
        }

        $items = $procurementRequest->items()->get()->keyBy('id');
        $awardsByItem = collect($validated['awards'])->keyBy('procurement_item_id');
        if ($awardsByItem->count() !== $items->count() || $awardsByItem->keys()->diff($items->keys())->isNotEmpty()) {
            throw ValidationException::withMessages(['awards' => 'Selecione exatamente uma proposta para cada item da cotação.']);
        }

        foreach ($items as $item) {
            $award = $awardsByItem->get($item->id);
            $selectedQuote = $submittedQuotes->get($award['supplier_quote_id']);
            $selectedQuoteItem = $selectedQuote?->items->firstWhere('procurement_item_id', $item->id);
            $lowestUnitPrice = $submittedQuotes
                ->map(fn (SupplierQuote $quote) => $quote->items->firstWhere('procurement_item_id', $item->id)?->unit_price)
                ->filter(fn ($price) => $price !== null)
                ->min();

            if (! $selectedQuoteItem || $lowestUnitPrice === null || (float) $selectedQuoteItem->unit_price > (float) $lowestUnitPrice) {
                throw ValidationException::withMessages(['awards' => "A proposta selecionada para {$item->description} não possui o menor valor por item."]);
            }
        }
    }
}
