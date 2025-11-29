<?php

declare(strict_types=1);

namespace App\Adhesion\Command;

class PersistAdhesionEmailCommand
{
    private string $email;
    public ?string $utmSource = null;
    public ?string $utmCampaign = null;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = mb_strtolower($email);
    }
}
