<?php

namespace App\Exception;

use Ramsey\Uuid\UuidInterface;

final class AdherentAlreadyEnabledException extends AdherentException
{
    public function __construct(UuidInterface $adherentUuid, \Exception $previous = null)
    {
        parent::__construct(
            $adherentUuid,
            sprintf('Adherent "%s" is already enabled.', $adherentUuid),
            $previous
        );
    }
}
