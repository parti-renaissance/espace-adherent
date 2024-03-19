<?php

namespace App\Procuration\Command;

class PersistProcurationEmailCommand
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $utmSource = null,
        public readonly ?string $utmCampaign = null,
        public ?string $clientIp = null
    ) {
    }
}
