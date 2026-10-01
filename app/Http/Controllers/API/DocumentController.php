<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NoticeAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Display a listing of document attachments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou sem condomínio associado.'], 403);
        }

        // Tipos de arquivo considerados como 'documento' para esta listagem
        $documentFileTypes = ['document', 'pdf', 'text', 'spreadsheet', 'presentation']; // Pode ser configurável

        $query = NoticeAttachment::query()
            ->join('notices', 'notice_attachments.notice_id', '=', 'notices.id')
            ->where('notices.condominium_id', $user->condominium_id)
            ->whereIn('notice_attachments.file_type', $documentFileTypes)
            ->select('notice_attachments.*', 'notices.title as notice_title', 'notices.created_at as notice_created_at')
            ->orderBy('notices.created_at', 'desc')
            ->orderBy('notice_attachments.original_name', 'asc');

        // Filtro por nome do arquivo
        if ($request->has('file_name')) {
            $query->where('notice_attachments.original_name', 'like', '%' . $request->input('file_name') . '%');
        }

        // Filtro por título do aviso
        if ($request->has('notice_title')) {
            $query->where('notices.title', 'like', '%' . $request->input('notice_title') . '%');
        }

        // Filtro por data do aviso (ex: YYYY-MM-DD)
        if ($request->has('notice_date')) {
            $query->whereDate('notices.created_at', $request->input('notice_date'));
        }


        $documents = $query->paginate($request->input('per_page', 15));

        return response()->json($documents);
    }
}
