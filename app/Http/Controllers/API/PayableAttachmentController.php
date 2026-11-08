<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payable;
use App\Models\PayableAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayableAttachmentController extends Controller
{
    /**
     * Display a listing of attachments for a specific payable.
     */
    public function index(Payable $payable)
    {
        return response()->json($payable->attachments);
    }

    /**
     * Store a newly created attachment in storage.
     */
    public function store(Request $request, Payable $payable)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $filePath = $file->store('payables/attachments', 'public');

        $attachment = $payable->attachments()->create([
            'condominium_id' => $payable->condominium_id,
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
    public function show(Payable $payable, PayableAttachment $attachment)
    {
        if ($payable->id !== $attachment->payable_id) {
            return response()->json(['message' => 'Attachment not found for this payable.'], 404);
        }

        return response()->json($attachment);
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Payable $payable, PayableAttachment $attachment)
    {
        if ($payable->id !== $attachment->payable_id) {
            return response()->json(['message' => 'Attachment not found for this payable.'], 404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(null, 204);
    }
}
