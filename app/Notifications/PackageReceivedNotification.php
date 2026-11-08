<?php

namespace App\Notifications;

use App\Models\Package;

class PackageReceivedNotification extends CondoNotification
{
    public function __construct(
        private readonly int $condominiumId,
        private readonly int $packageId,
        private readonly int $apartmentId,
        private readonly string $recipientApartment,
        private readonly ?string $code,
        private readonly ?int $actorId,
    ) {}

    protected function buildPayload(): array
    {
        $body = "Unidade {$this->recipientApartment}";
        if ($this->code !== null && $this->code !== '') {
            $body .= " — código {$this->code}";
        }

        return array_merge(
            $this->basePayload(
                $this->condominiumId,
                'packages',
                'Nova encomenda',
                $body,
                "/packages/{$this->packageId}",
                "package.received:{$this->packageId}",
                $this->actorId,
                Package::class,
                $this->packageId,
            ),
            [
                'apartment_id' => $this->apartmentId,
                'code' => $this->code,
            ],
        );
    }
}
