<?php

namespace App\Procuration\Command;

use App\Procuration\V2\InitialRequestTypeEnum;

class PersistProcurationEmailCommand
{
    private string $email;
    public InitialRequestTypeEnum $type;
    public ?string $utmSource = null;
    public ?string $utmCampaign = null;
    public ?string $clientIp = null;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = mb_strtolower(trim($email));
    }
}
