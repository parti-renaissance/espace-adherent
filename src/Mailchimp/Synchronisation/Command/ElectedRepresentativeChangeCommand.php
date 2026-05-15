<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use Symfony\Component\Uid\Uuid;

class ElectedRepresentativeChangeCommand implements ElectedRepresentativeChangeCommandInterface
{
    private $uuid;
    private $oldEmailAddress;

    public function __construct(Uuid $uuid, ?string $oldEmailAddress = null)
    {
        $this->uuid = $uuid;
        $this->oldEmailAddress = $oldEmailAddress;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getOldEmailAddress(): ?string
    {
        return $this->oldEmailAddress;
    }
}
