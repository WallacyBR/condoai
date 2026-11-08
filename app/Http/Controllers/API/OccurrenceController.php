<?php

namespace App\Http\Controllers\API;

use App\Enums\OccurrencePriority;
use App\Enums\OccurrenceStatus;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\Occurrence;
use App\Models\OccurrenceComment;
use App\Models\OccurrencePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;

class OccurrenceController extends Controller
{
    use ResolvesTenantCondominium;

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Display a listing of the occurrences.
     *
     * @queryParam status string Filter by status. Available values: open, in_progress, resolved, closed, reopened. Example: open
     * @queryParam priority string Filter by priority. Available values: low, medium, high, urgent. Example: high
     * @queryParam user_id int Filter by user ID. Example: 1
     * @queryParam order_by string Field to order by. Example: created_at
     * @queryParam order_direction string Direction of ordering (asc/desc). Example: desc
     * @queryParam per_page int Number of items per page. Example: 15
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Occurrence::class);

        $request->validate([
            'status' => ['sometimes', 'string', new Enum(OccurrenceStatus::class)],
            'priority' => ['sometimes', 'string', new Enum(OccurrencePriority::class)],
            'user_id' => 'sometimes|integer|exists:users,id',
            'order_by' => 'sometimes|string',
            'order_direction' => 'sometimes|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1',
        ]);

        $occurrences = Occurrence::with(['user:id,name', 'photos', 'comments.user:id,name'])
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->has('priority'), function ($query) use ($request) {
                return $query->where('priority', $request->priority);
            })
            ->when($request->has('user_id'), function ($query) use ($request) {
                return $query->where('user_id', $request->user_id);
            })
            ->orderBy(
                $request->input('order_by', 'created_at'),
                $request->input('order_direction', 'desc')
            )
            ->paginate($request->input('per_page', 15));

        return response()->json($occurrences);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Store a newly created occurrence in storage.
     *
     * @bodyParam title string required The title of the occurrence. Example: Lâmpada queimada no bloco A
     * @bodyParam description string required A detailed description of the occurrence. Example: A lâmpada do corredor do primeiro andar do bloco A está queimada.
     * @bodyParam priority string required The priority of the occurrence. Example: medium
     * @bodyParam photos file[] An array of photos for the occurrence.
     * @bodyParam photos.* file A photo for the occurrence.
     * @bodyParam photo_descriptions string[] Descriptions for each photo. Should have the same number of elements as photos. Example: ["Foto da lâmpada queimada", "Corredor escuro"]
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Occurrence::class);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', new Enum(OccurrencePriority::class)],
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'photo_descriptions' => 'nullable|array',
            'photo_descriptions.*' => 'nullable|string|max:255',
        ]);

        $occurrence = Occurrence::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'priority' => $validatedData['priority'],
            'status' => OccurrenceStatus::OPEN,
            'user_id' => Auth::id(),
            'condominium_id' => $this->tenantCondominiumId($request),
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photoFile) {
                $path = $photoFile->store('occurrence_photos', 'public');
                $description = $validatedData['photo_descriptions'][$index] ?? null;
                $occurrence->photos()->create([
                    'photo_path' => $path,
                    'description' => $description,
                ]);
            }
        }

        $occurrence->load(['user:id,name', 'photos', 'comments.user:id,name']);

        return response()->json($occurrence, 201);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Display the specified occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Occurrence $occurrence)
    {
        $this->authorize('view', $occurrence);
        $occurrence->load(['user:id,name', 'photos', 'comments.user:id,name', 'condominium:id,name']);

        return response()->json($occurrence);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Update the specified occurrence in storage.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     *
     * @bodyParam title string The title of the occurrence. Example: Lâmpada do poste P-12 queimada
     * @bodyParam description string A detailed description of the occurrence.
     * @bodyParam priority string The priority of the occurrence. Example: high
     * @bodyParam status string The status of the occurrence. Example: in_progress
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Occurrence $occurrence)
    {
        $this->authorize('update', $occurrence);

        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'priority' => ['sometimes', new Enum(OccurrencePriority::class)],
            'status' => ['sometimes', new Enum(OccurrenceStatus::class)],
        ]);

        $occurrence->update($validatedData);

        $occurrence->load(['user:id,name', 'photos', 'comments.user:id,name']);

        return response()->json($occurrence);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Remove the specified occurrence from storage.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Occurrence $occurrence)
    {
        $this->authorize('delete', $occurrence);

        // Delete associated photos from storage
        foreach ($occurrence->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
            $photo->delete();
        }

        $occurrence->delete();

        return response()->json(null, 204);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Add a photo to an existing occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     *
     * @bodyParam photo file required The photo file to upload.
     * @bodyParam description string A description for the photo.
     *
     * @return \Illuminate\Http\Response
     */
    public function addPhoto(Request $request, Occurrence $occurrence)
    {
        $this->authorize('update', $occurrence);

        $validatedData = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string|max:255',
        ]);

        $path = $request->file('photo')->store('occurrence_photos', 'public');

        $photo = $occurrence->photos()->create([
            'photo_path' => $path,
            'description' => $validatedData['description'] ?? null,
        ]);

        return response()->json($photo, 201);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Delete a photo from an occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @urlParam photo integer required The ID of the photo to delete. Example: 1
     *
     * @return \Illuminate\Http\Response
     */
    public function deletePhoto(Occurrence $occurrence, OccurrencePhoto $photo)
    {
        $this->authorize('update', $occurrence);

        if ($photo->occurrence_id !== $occurrence->id) {
            return response()->json(['message' => 'Foto não pertence a esta ocorrência.'], 403);
        }

        Storage::disk('public')->delete($photo->photo_path);
        $photo->delete();

        return response()->json(null, 204);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Add a comment to an existing occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     *
     * @bodyParam comment string required The comment text. Example: Verifiquei a lâmpada, realmente está queimada.
     *
     * @return \Illuminate\Http\Response
     */
    public function addComment(Request $request, Occurrence $occurrence)
    {
        $this->authorize('create', [OccurrenceComment::class, $occurrence]);

        $validatedData = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = $occurrence->comments()->create([
            'comment' => $validatedData['comment'],
            'user_id' => Auth::id(),
        ]);

        $comment->load('user:id,name');

        return response()->json($comment, 201);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Delete a comment from an occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @urlParam comment integer required The ID of the comment to delete. Example: 1
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteComment(Occurrence $occurrence, OccurrenceComment $comment)
    {
        // Ensure the comment belongs to the occurrence
        if ($comment->occurrence_id !== $occurrence->id) {
            return response()->json(['message' => 'Comentário não pertence a esta ocorrência.'], 403);
        }

        $this->authorize('delete', $comment); // Policy on OccurrenceComment model

        $comment->delete();

        return response()->json(null, 204);
    }

    /**
     * @group Ocorrências
     *
     * @authenticated
     * Converte uma ocorrência aberta em Ordem de Manutenção / Agendamento.
     *
     * @urlParam occurrence integer required ID da ocorrência. Example: 1
     *
     * @bodyParam type string Tipo de manutenção (preventiva, corretiva, emergencial). Example: corretiva
     * @bodyParam start_date date Data de início prevista. Example: 2026-10-02
     * @bodyParam end_date date Data de conclusão prevista. Example: 2026-10-04
     * @bodyParam due_time string Horário previsto. Example: 14:00
     * @bodyParam maintenance_equipment_id integer ID do equipamento vinculado (opcional). Example: 2
     * @bodyParam assigned_user_ids integer[] IDs dos usuários técnicos/manutencistas atribuídos. Example: [1, 2]
     * @bodyParam notes string Observações adicionais para a equipe técnica. Example: Verificar vazamento no 3º andar
     *
     * @return \Illuminate\Http\Response
     */
    public function convertToMaintenance(Request $request, Occurrence $occurrence)
    {
        $this->authorize('update', $occurrence);

        $validated = $request->validate([
            'type' => 'nullable|string|in:preventiva,corretiva,emergencial',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'due_time' => 'nullable|string',
            'maintenance_equipment_id' => 'nullable|exists:maintenance_equipments,id',
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $schedule = MaintenanceSchedule::create([
            'condominium_id' => $occurrence->condominium_id,
            'occurrence_id' => $occurrence->id,
            'maintenance_equipment_id' => $validated['maintenance_equipment_id'] ?? null,
            'title' => "[Ocorrência #{$occurrence->id}] {$occurrence->title}",
            'description' => $occurrence->description.(! empty($validated['notes']) ? "\n\nNotas adicionais: ".$validated['notes'] : ''),
            'type' => $validated['type'] ?? 'corretiva',
            'start_date' => $validated['start_date'] ?? now()->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->addDays(2)->toDateString(),
            'due_time' => $validated['due_time'] ?? '17:00',
            'is_recurring' => false,
        ]);

        if (! empty($validated['assigned_user_ids'])) {
            $schedule->responsibleUsers()->sync($validated['assigned_user_ids']);
        }

        $occurrence->update([
            'status' => OccurrenceStatus::IN_PROGRESS,
            'converted_to_maintenance_id' => $schedule->id,
        ]);

        $occurrence->comments()->create([
            'user_id' => Auth::id(),
            'comment' => "Ocorrência convertida na Ordem de Manutenção #{$schedule->id}.",
        ]);

        return response()->json([
            'message' => 'Ocorrência convertida em ordem de manutenção com sucesso.',
            'schedule' => $schedule->load(['equipment', 'responsibleUsers']),
            'occurrence' => $occurrence->fresh(['user:id,name', 'photos', 'comments.user:id,name', 'maintenanceSchedule']),
        ], 201);
    }
}
