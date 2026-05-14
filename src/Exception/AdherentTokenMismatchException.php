<?php

declare(strict_types=1);

namespace App\Exception;

use App\Entity\AdherentExpirableTokenInterface;
use Symfony\Component\Uid\Uuid;

final class AdherentTokenMismatchException extends AdherentTokenException
{
    private $unexpectedAdherentUuid;

    public function __construct(
        AdherentExpirableTokenInterface $token,
        Uuid $unexpectedAdherentUuid,
        ?\Exception $previous = null,
    ) {
        $message = \sprintf(
            'The %s token %s cannot be used by the adherent %s but only by adherent %s.',
            $token->getType(),
            $token->getValue(),
            $unexpectedAdherentUuid,
            $token->getAdherentUuid()
        );

        parent::__construct($token, $message, $previous);

        $this->unexpectedAdherentUuid = $unexpectedAdherentUuid;
    }

    public function getUnexpectedAdherentUuid(): Uuid
    {
        return $this->unexpectedAdherentUuid;
    }
}
