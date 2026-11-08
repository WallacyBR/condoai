<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\NoticeAttachment;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);

        $documentFileTypes = ['document', 'pdf', 'text', 'spreadsheet', 'presentation'];

        $query = NoticeAttachment::query()
            ->join('notices', 'notice_attachments.notice_id', '=', 'notices.id')
            ->where('notices.condominium_id', $condominiumId)
            ->whereIn('notice_attachments.file_type', $documentFileTypes)
            ->select('notice_attachments.*', 'notices.title as notice_title', 'notices.created_at as notice_created_at')
            ->orderBy('notices.created_at', 'desc')
            ->orderBy('notice_attachments.original_name', 'asc');

        if ($request->has('file_name')) {
            $query->where('notice_attachments.original_name', 'like', '%'.$request->input('file_name').'%');
        }

        if ($request->has('notice_title')) {
            $query->where('notices.title', 'like', '%'.$request->input('notice_title').'%');
        }

        if ($request->has('notice_date')) {
            $query->whereDate('notices.created_at', $request->input('notice_date'));
        }

        return response()->json($query->paginate($request->input('per_page', 15)));
    }
}
