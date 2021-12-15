<?php

namespace App\Api\DTO;

use App\Validator\AdherentUuid as ValidAdherentUuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AdherentUuid
{
    /**
     * @Assert\NotBlank
     * @ValidAdherentUuid
     */
    public ?UuidInterface $adherentUuid = null;
}
