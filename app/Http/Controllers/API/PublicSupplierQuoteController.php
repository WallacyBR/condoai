<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProcurementInvitation;
use App\Models\Supplier;
use App\Models\SupplierQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicSupplierQuoteController extends Controller
{
    /**
     * Exibe o formulário público de resposta da cotação.
     */
    public function show(string $token)
    {
        $invitation = $this->invitation($token);
        $invitation->update(['last_opened_at' => now()]);
        $request = $invitation->procurementRequest()->with('items')->firstOrFail();

        return response()->json([
            'code' => $request->code,
            'title' => $request->title,
            'description' => $request->description,
            'response_deadline' => $request->response_deadline->toDateString(),
            'supplier_name' => $invitation->supplier_name,
            'items' => $request->items->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'specification' => $item->specification,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
            ]),
            'quote' => $invitation->quote()->with('items', 'attachments')->first(),
        ]);
    }

    /**
     * Salva ou envia a proposta do fornecedor pelo token público.
     */
    public function store(Request $request, string $token)
    {
        $invitation = $this->invitation($token);
        $procurement = $invitation->procurementRequest()->with('items')->firstOrFail();
        $validated = $request->validate([
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_document' => ['nullable', 'string', 'max:30'],
            'supplier_email' => ['nullable', 'email', 'max:255'],
            'supplier_phone' => ['nullable', 'string', 'max:50'],
            'freight_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'delivery_terms' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'submit' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.procurement_item_id' => ['required', 'integer'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);
        foreach ($validated['items'] as $item) {
            if (! $procurement->items->contains('id', $item['procurement_item_id'])) {
                throw ValidationException::withMessages(['items' => 'Um item informado não pertence a esta cotação.']);
            }
        }

        return DB::transaction(function () use ($validated, $invitation, $procurement) {
            $supplier = Supplier::withoutGlobalScopes()->firstOrCreate([
                'condominium_id' => $procurement->condominium_id,
                'document' => $validated['supplier_document'] ?? null,
            ], [
                'name' => $validated['supplier_name'],
                'email' => $validated['supplier_email'] ?? null,
                'phone' => $validated['supplier_phone'] ?? null,
            ]);
            $quote = SupplierQuote::updateOrCreate(['procurement_invitation_id' => $invitation->id], [
                'supplier_id' => $supplier->id,
                'supplier_name' => $validated['supplier_name'],
                'supplier_document' => $validated['supplier_document'] ?? null,
                'supplier_email' => $validated['supplier_email'] ?? null,
                'supplier_phone' => $validated['supplier_phone'] ?? null,
                'freight_amount' => $validated['freight_amount'] ?? 0,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => ! empty($validated['submit']) ? 'submitted' : 'draft',
                'submitted_at' => ! empty($validated['submit']) ? now() : null,
            ]);
            foreach ($validated['items'] as $item) {
                $procurementItem = $procurement->items->firstWhere('id', $item['procurement_item_id']);
                $quote->items()->updateOrCreate(['procurement_item_id' => $procurementItem->id], [
                    'unit_price' => $item['unit_price'] ?? null,
                    'quantity' => $procurementItem->quantity,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return response()->json($quote->load('items'), 201);
        });
    }

    public function upload(Request $request, string $token)
    {
        $invitation = $this->invitation($token);
        $request->validate(['file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240']]);
        $quote = $invitation->quote()->firstOrFail();
        $file = $request->file('file');
        $path = $file->store('procurement/quotes', 'public');

        return response()->json($quote->attachments()->create([
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]), 201);
    }

    private function invitation(string $token): ProcurementInvitation
    {
        $invitation = ProcurementInvitation::withoutGlobalScopes()->where('token', $token)->with('procurementRequest')->firstOrFail();
        abort_if($invitation->expires_at->isPast() || $invitation->procurementRequest->status !== 'published', 410, 'Este link de cotação não está mais disponível.');

        return $invitation;
    }
}
