<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payable;
use App\Models\PayableAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

class PayableController extends Controller
{
    public function index(Request $request)
    {
        $query = Payable::with('attachments');
        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }
        return $query->orderBy('due_date')->get();
    }

    private function handleAttachments(Payable $payable, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store('payables', 'public');
                PayableAttachment::create([
                    'condominium_id' => $payable->condominium_id,
                    'payable_id' => $payable->id,
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
        $payable = Payable::create($validated);
        $this->handleAttachments($payable, $attachments);
        return $payable->load('attachments');
    }

    public function show(Payable $payable)
    {
        return $payable->load('attachments');
    }

    public function update(Request $request, Payable $payable)
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
        $payable->update($validated);
        $this->handleAttachments($payable, $attachments);
        return $payable->load('attachments');
    }

    public function destroy(Payable $payable)
    {
        $payable->delete();
        return response()->json(null, 204);
    }
}
