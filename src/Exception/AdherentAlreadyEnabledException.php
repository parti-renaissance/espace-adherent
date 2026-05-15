<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Uid\Uuid;

final class AdherentAlreadyEnabledException extends AdherentException
{
    public function __construct(Uuid $adherentUuid, ?\Exception $previous = null)
    {
        parent::__construct(
            $adherentUuid,
            \sprintf('Adherent "%s" is already enabled.', $adherentUuid),
            $previous
        );
    }
}
