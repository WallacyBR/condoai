<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Receivable;
use App\Models\ReceivableAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReceivableAttachmentController extends Controller
{
    /**
     * Display a listing of attachments for a specific receivable.
     */
    public function index(Receivable $receivable)
    {
        return response()->json($receivable->attachments);
    }

    /**
     * Store a newly created attachment in storage.
     */
    public function store(Request $request, Receivable $receivable)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $filePath = $file->store('receivables/attachments', 'public');

        $attachment = $receivable->attachments()->create([
            'condominium_id' => $receivable->condominium_id,
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
    public function show(Receivable $receivable, ReceivableAttachment $attachment)
    {
        if ($receivable->id !== $attachment->receivable_id) {
            return response()->json(['message' => 'Attachment not found for this receivable.'], 404);
        }

        return response()->json($attachment);
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Receivable $receivable, ReceivableAttachment $attachment)
    {
        if ($receivable->id !== $attachment->receivable_id) {
            return response()->json(['message' => 'Attachment not found for this receivable.'], 404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(null, 204);
    }
}
