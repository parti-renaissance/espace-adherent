<?php

declare(strict_types=1);

namespace App\Procuration\Command;

use App\Procuration\InitialRequestTypeEnum;

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
