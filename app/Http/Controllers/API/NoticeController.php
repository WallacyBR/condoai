<?php

namespace App\Http\Controllers\API;

use App\Events\Notices\NoticeWasPublished;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Block;
use App\Models\Notice;
use App\Models\Tower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NoticeController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $query = Notice::query()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc');

        if ($request->has('title')) {
            $query->where('title', 'like', '%'.$request->input('title').'%');
        }

        return response()->json($query->paginate($request->input('per_page', 15)));
    }

    public function store(Request $request)
    {
        $condominiumId = $this->tenantCondominiumId($request);
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'send_to_all' => 'sometimes|boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:10240',
            'targets' => 'nullable|array',
            'targets.*.type' => 'required_with:targets|string|in:tower,block,apartment',
            'targets.*.id' => 'required_with:targets|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $notice = Notice::create([
            'condominium_id' => $condominiumId,
            'user_id' => $user->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'send_to_all' => $data['send_to_all'] ?? false,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
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

        if (! ($data['send_to_all'] ?? false) && ! empty($data['targets'])) {
            foreach ($data['targets'] as $target) {
                $modelClass = match ($target['type']) {
                    'tower' => Tower::class,
                    'block' => Block::class,
                    'apartment' => Apartment::class,
                    default => null,
                };

                if ($modelClass) {
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

        event(new NoticeWasPublished(
            condominiumId: $condominiumId,
            actorId: $user->id,
            noticeId: $notice->id,
        ));

        return response()->json($notice, 201);
    }

    public function show(Notice $notice)
    {
        $notice->load('user:id,name', 'attachments', 'towers', 'blocks', 'apartments');

        return response()->json($notice);
    }

    public function update(Request $request, Notice $notice)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'send_to_all' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        unset($data['condominium_id']);
        $notice->update($data);

        $notice->load('user:id,name', 'attachments', 'towers', 'blocks', 'apartments');

        return response()->json($notice);
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();

        return response()->json(['message' => 'Aviso excluído com sucesso.'], 200);
    }

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
                return 'document';
            case 'xls':
            case 'xlsx':
                return 'spreadsheet';
            case 'ppt':
            case 'pptx':
                return 'presentation';
            case 'txt':
                return 'text';
        }
        if (str_contains($mimeType, 'document') || str_contains($mimeType, 'application/pdf') || str_contains($mimeType, 'application/msword')) {
            return 'document';
        }

        return 'file';
    }
}
