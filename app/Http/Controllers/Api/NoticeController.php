<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Adicionar outros Models que serão usados, como Tower, Block, Apartment
use App\Models\Tower;
use App\Models\Block;
use App\Models\Apartment;
use App\Models\Condominium;

class NoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Lógica para listar avisos do condomínio logado
        // com paginação e filtros
        $user = Auth::user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou sem condomínio associado.'], 403);
        }

        $query = Notice::where('condominium_id', $user->condominium_id)
                       ->with('user:id,name') // Carregar criador do aviso
                       ->orderBy('created_at', 'desc');

        // Exemplo de filtro por título (pode ser expandido)
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        $notices = $query->paginate($request->input('per_page', 15));

        return response()->json($notices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->condominium_id) {
            return response()->json(['message' => 'Usuário não autenticado ou sem condomínio associado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'send_to_all' => 'sometimes|boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:10240', // Max 10MB por arquivo
            'targets' => 'nullable|array', // Usado se send_to_all = false
            'targets.*.type' => 'required_with:targets|string|in:tower,block,apartment',
            'targets.*.id' => 'required_with:targets|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $condominiumId = $user->condominium_id;

        $notice = Notice::create([
            'condominium_id' => $condominiumId,
            'user_id' => $user->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'send_to_all' => $data['send_to_all'] ?? false,
        ]);

        // Processar anexos
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Storage path: notices/{condominium_id}/{notice_id}/{file_name}
                $filePath = $file->store("notices/{$condominiumId}/{$notice->id}", 'public');
                $notice->attachments()->create([
                    'file_path' => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_type' => $this->determineFileType($file->getClientMimeType(), $file->getClientOriginalExtension()),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Processar targets se send_to_all for false
        if (!($data['send_to_all'] ?? false) && !empty($data['targets'])) {
            foreach ($data['targets'] as $target) {
                $modelClass = null;
                switch ($target['type']) {
                    case 'tower':
                        $modelClass = Tower::class;
                        break;
                    case 'block':
                        $modelClass = Block::class;
                        break;
                    case 'apartment':
                        $modelClass = Apartment::class;
                        break;
                }

                if ($modelClass) {
                    // Verificar se o target pertence ao mesmo condomínio
                    $targetModel = $modelClass::where('id', $target['id'])
                                              ->where('condominium_id', $condominiumId)
                                              ->first();
                    if ($targetModel) {
                        $notice->{$target['type'].'s'}()->attach($targetModel->id);
                    }
                }
            }
        }

        $notice->load('attachments', 'user:id,name', 'towers', 'blocks', 'apartments');
        return response()->json($notice, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notice $notice)
    {
        $user = Auth::user();
        if (!$user || $notice->condominium_id !== $user->condominium_id) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $notice->load('user:id,name', 'attachments', 'towers', 'blocks', 'apartments');
        return response()->json($notice);
    }

    /**
     * Update the specified resource in storage.
     * (Escopo para futuras implementações, se necessário)
     */
    public function update(Request $request, Notice $notice)
    {
        $user = Auth::user();
        if (!$user || $notice->condominium_id !== $user->condominium_id) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'send_to_all' => 'sometimes|boolean',
            // Anexos e targets podem ser mais complexos de atualizar,
            // pode envolver remover antigos e adicionar novos.
            // Por simplicidade, não implementado na v1.
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $notice->update($data);

        // Lógica para atualizar anexos e targets (se necessário)
        // ...

        $notice->load('user:id,name', 'attachments', 'towers', 'blocks', 'apartments');
        return response()->json($notice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notice $notice)
    {
        $user = Auth::user();
        if (!$user || $notice->condominium_id !== $user->condominium_id) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        // Soft delete irá disparar eventos se houver observadores
        // e manterá os registros no banco de dados.
        // Anexos físicos podem precisar de limpeza separada se não forem soft deleted
        // ou se a política for de exclusão física.
        // Por enquanto, a FK com onDelete('cascade') na migration de attachments
        // não se aplica a softDeletes do notice. Se o notice for forceDeleted, aí sim.
        // A tabela noticables também tem onDelete('cascade') para exclusão física.

        // Opcional: Remover arquivos físicos se o aviso for permanentemente excluído no futuro.
        // if ($notice->isForceDeleting()) {
        //     foreach ($notice->attachments as $attachment) {
        //         Storage::disk('public')->delete($attachment->file_path);
        //     }
        // }

        $notice->delete(); // Soft delete

        return response()->json(['message' => 'Aviso excluído com sucesso.'], 200);
    }

    /**
     * Helper para determinar o tipo de arquivo baseado no MIME type ou extensão.
     */
    private function determineFileType(string $mimeType, string $extension): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        switch (strtolower($extension)) {
            case 'pdf':
                return 'pdf';
            case 'doc':
            case 'docx':
                return 'document'; // ou 'word'
            case 'xls':
            case 'xlsx':
                return 'spreadsheet'; // ou 'excel'
            case 'ppt':
            case 'pptx':
                return 'presentation'; // ou 'powerpoint'
            case 'txt':
                return 'text';
        }
        if (str_contains($mimeType, 'document') || str_contains($mimeType, 'application/pdf') || str_contains($mimeType, 'application/msword')) {
            return 'document';
        }
        return 'file'; // Tipo genérico
    }
}
