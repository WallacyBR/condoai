<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Occurrence;
use App\Models\OccurrencePhoto;
use App\Models\OccurrenceComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Enums\OccurrenceStatus;
use App\Enums\OccurrencePriority;
use Illuminate\Validation\Rules\Enum;

class OccurrenceController extends Controller
{
    /**
     * @group Ocorrências
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
            'condominium_id' => Auth::user()->condominium_id,
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
     * @authenticated
     * Display the specified occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @param  \App\Models\Occurrence  $occurrence
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
     * @authenticated
     * Update the specified occurrence in storage.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @bodyParam title string The title of the occurrence. Example: Lâmpada do poste P-12 queimada
     * @bodyParam description string A detailed description of the occurrence.
     * @bodyParam priority string The priority of the occurrence. Example: high
     * @bodyParam status string The status of the occurrence. Example: in_progress
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Occurrence  $occurrence
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
     * @authenticated
     * Remove the specified occurrence from storage.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @param  \App\Models\Occurrence  $occurrence
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
     * @authenticated
     * Add a photo to an existing occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @bodyParam photo file required The photo file to upload.
     * @bodyParam description string A description for the photo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Occurrence  $occurrence
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
     * @authenticated
     * Delete a photo from an occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @urlParam photo integer required The ID of the photo to delete. Example: 1
     *
     * @param  \App\Models\Occurrence  $occurrence
     * @param  \App\Models\OccurrencePhoto  $photo
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
     * @authenticated
     * Add a comment to an existing occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @bodyParam comment string required The comment text. Example: Verifiquei a lâmpada, realmente está queimada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Occurrence  $occurrence
     * @return \Illuminate\Http\Response
     */
    public function addComment(Request $request, Occurrence $occurrence)
    {
        $this->authorize('view', $occurrence); // Or a more specific "comment" permission

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
     * @authenticated
     * Delete a comment from an occurrence.
     *
     * @urlParam occurrence integer required The ID of the occurrence. Example: 1
     * @urlParam comment integer required The ID of the comment to delete. Example: 1
     *
     * @param  \App\Models\Occurrence  $occurrence
     * @param  \App\Models\OccurrenceComment  $comment
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
}
