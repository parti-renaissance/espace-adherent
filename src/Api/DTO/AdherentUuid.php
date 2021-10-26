<?php

namespace App\Api\DTO;

use Ramsey\Uuid\UuidInterface;

class AdherentUuid
{
    /**
     * @var UuidInterface
     */
    public $adherentUuid;

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    public function setAdherentUuid(UuidInterface $adherentUuid): void
    {
        $this->adherentUuid = $adherentUuid;
    }
}
