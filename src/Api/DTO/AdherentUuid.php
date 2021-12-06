<?php

namespace App\Api\DTO;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AdherentUuid
{
    /** @Assert\NotBlank */
    public ?UuidInterface $adherentUuid = null;
}
