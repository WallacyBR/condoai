<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationInboxController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $condominiumId = $this->tenantCondominiumId($request);

        $query = $user->notifications()
            ->where('data->condominium_id', $condominiumId)
            ->orderByDesc('created_at');

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $paginator = $query->paginate($request->integer('per_page', 15));

        $paginator->getCollection()->transform(function ($notification) {
            return $this->formatNotification($notification);
        });

        return response()->json($paginator);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $condominiumId = $this->tenantCondominiumId($request);

        $count = $user->unreadNotifications()
            ->where('data->condominium_id', $condominiumId)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user('api');
        $condominiumId = $this->tenantCondominiumId($request);

        $notification = $user->notifications()
            ->where('id', $id)
            ->where('data->condominium_id', $condominiumId)
            ->first();

        if ($notification === null) {
            return response()->json(['message' => 'Notificação não encontrada.'], 404);
        }

        $notification->markAsRead();

        return response()->json($this->formatNotification($notification->fresh()));
    }

    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $condominiumId = $this->tenantCondominiumId($request);

        $user->unreadNotifications()
            ->where('data->condominium_id', $condominiumId)
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Todas as notificações foram marcadas como lidas.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatNotification($notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
            'condominium_id' => $data['condominium_id'] ?? null,
            'category' => $data['category'] ?? null,
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'dedupe_key' => $data['dedupe_key'] ?? null,
            'actor_id' => $data['actor_id'] ?? null,
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
        ];
    }
}
