<?php

declare(strict_types=1);

namespace App\Api\DTO;

use App\Validator\AdherentUuid as ValidAdherentUuid;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdherentUuid
{
    #[Assert\NotBlank]
    #[ValidAdherentUuid]
    public ?Uuid $adherentUuid = null;
}
