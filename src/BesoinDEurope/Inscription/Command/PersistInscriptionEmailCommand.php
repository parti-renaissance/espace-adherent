<?php

namespace App\BesoinDEurope\Inscription\Command;

class PersistInscriptionEmailCommand
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $utmSource = null,
        public readonly ?string $utmCampaign = null,
        public ?string $clientIp = null
    ) {
    }
}
