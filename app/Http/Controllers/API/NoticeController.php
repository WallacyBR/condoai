<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Tower;
use App\Models\Block;
use App\Models\Apartment;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::query();

        if ($request->filled('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        if ($request->filled('target_type') && $request->filled('target_id')) {
            $class = $this->resolveClass($request->target_type);
            if ($class) {
                $query->whereHas('noticeTargets', function ($q) use ($class, $request) {
                    $q->where('target_type', $class)
                      ->where('target_id', $request->target_id);
                });
            }
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image_path' => 'nullable|string|max:255',
            'condominium_id' => 'required|exists:condominiums,id',
            'targets' => 'array',
            'targets.*.type' => 'required_with:targets|string',
            'targets.*.id' => 'required_with:targets|integer'
        ]);

        $targets = $validated['targets'] ?? [];
        unset($validated['targets']);

        $notice = Notice::create($validated);

        foreach ($targets as $target) {
            $class = $this->resolveClass($target['type'] ?? '');
            if ($class) {
                $notice->noticeTargets()->create([
                    'target_type' => $class,
                    'target_id' => $target['id']
                ]);
            }
        }

        return response()->json($notice->load('noticeTargets'), 201);
    }

    public function show(Notice $notice)
    {
        return $notice->load('noticeTargets');
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'body' => 'string',
            'image_path' => 'nullable|string|max:255',
        ]);

        $notice->update($validated);

        return $notice->load('noticeTargets');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return response()->json(null, 204);
    }

    private function resolveClass(string $type): ?string
    {
        return [
            'tower' => Tower::class,
            'block' => Block::class,
            'apartment' => Apartment::class,
        ][$type] ?? null;
    }
}
