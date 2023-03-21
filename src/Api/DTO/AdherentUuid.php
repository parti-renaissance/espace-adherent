<?php

namespace App\Api\DTO;

use App\Validator\AdherentUuid as ValidAdherentUuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AdherentUuid
{
    /**
     * @ValidAdherentUuid
     */
    #[Assert\NotBlank]
    public ?UuidInterface $adherentUuid = null;
}
