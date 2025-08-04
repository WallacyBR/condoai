<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseRequestAttachmentController extends Controller
{
    /**
     * Display a listing of attachments for a specific expense request.
     */
    public function index(ExpenseRequest $expenseRequest)
    {
        return response()->json($expenseRequest->attachments);
    }

    /**
     * Store a newly created attachment in storage.
     */
    public function store(Request $request, ExpenseRequest $expenseRequest)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $filePath = $file->store('expense_requests/attachments', 'public');

        $attachment = $expenseRequest->attachments()->create([
            'condominium_id' => $expenseRequest->condominium_id,
            'file_path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json($attachment, 201);
    }

    /**
     * Display the specified attachment.
     */
    public function show(ExpenseRequest $expenseRequest, ExpenseRequestAttachment $attachment)
    {
        if ($expenseRequest->id !== $attachment->expense_request_id) {
            return response()->json(['message' => 'Attachment not found for this expense request.'], 404);
        }
        return response()->json($attachment);
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(ExpenseRequest $expenseRequest, ExpenseRequestAttachment $attachment)
    {
        if ($expenseRequest->id !== $attachment->expense_request_id) {
            return response()->json(['message' => 'Attachment not found for this expense request.'], 404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(null, 204);
    }
}
