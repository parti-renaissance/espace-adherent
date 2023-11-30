<?php

namespace App\Adhesion\Command;

class PersistAdhesionEmailCommand
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $utmSource = null,
        public readonly ?string $utmCampaign = null,
    ) {
    }
}
