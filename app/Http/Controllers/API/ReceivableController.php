<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Receivable;
use App\Models\ReceivableAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $query = Receivable::with('attachments');
        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }
        return $query->orderBy('due_date')->get();
    }

    private function handleAttachments(Receivable $receivable, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store('receivables', 'public');
                ReceivableAttachment::create([
                    'condominium_id' => $receivable->condominium_id,
                    'receivable_id' => $receivable->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => 'document',
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'condominium_id' => ['required', Rule::exists('condominiums', 'id')],
            'chart_account_id' => ['required', Rule::exists('chart_accounts', 'id')],
            'bank_id' => ['nullable', Rule::exists('banks', 'id')],
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'attachments.*' => 'file',
        ]);

        $attachments = $request->file('attachments', []);
        $receivable = Receivable::create($validated);
        $this->handleAttachments($receivable, $attachments);
        return $receivable->load('attachments');
    }

    public function show(Receivable $receivable)
    {
        return $receivable->load('attachments');
    }

    public function update(Request $request, Receivable $receivable)
    {
        $validated = $request->validate([
            'condominium_id' => ['sometimes', Rule::exists('condominiums', 'id')],
            'chart_account_id' => ['sometimes', Rule::exists('chart_accounts', 'id')],
            'bank_id' => ['nullable', Rule::exists('banks', 'id')],
            'due_date' => 'date',
            'amount' => 'numeric|min:0.01',
            'status' => 'string',
            'description' => 'nullable|string',
            'attachments.*' => 'file',
        ]);

        $attachments = $request->file('attachments', []);
        $receivable->update($validated);
        $this->handleAttachments($receivable, $attachments);
        return $receivable->load('attachments');
    }

    public function destroy(Receivable $receivable)
    {
        $receivable->delete();
        return response()->json(null, 204);
    }
}
