<?php

namespace App\Procuration\Command;

use App\Procuration\V2\InitialRequestTypeEnum;

class PersistProcurationEmailCommand
{
    public function __construct(
        public readonly string $email,
        public readonly InitialRequestTypeEnum $type,
        public readonly ?string $utmSource = null,
        public readonly ?string $utmCampaign = null,
        public ?string $clientIp = null
    ) {
    }
}
