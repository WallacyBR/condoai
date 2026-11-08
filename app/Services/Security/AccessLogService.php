<?php

namespace App\Services\Security;

use App\Models\AccessLog;
use App\Models\User;
use Illuminate\Http\Request;

class AccessLogService
{
    public function record(
        Request $request,
        string $method,
        string $status,
        ?User $user = null,
        ?string $email = null,
        ?string $reason = null,
        array $metadata = []
    ): AccessLog {
        return AccessLog::create([
            'user_id' => $user?->id,
            'condominium_id' => $user?->condominium_id,
            'email' => $email ?? $user?->email,
            'method' => $method,
            'status' => $status,
            'reason' => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata === [] ? null : $metadata,
            'occurred_at' => now(),
        ]);
    }
}
