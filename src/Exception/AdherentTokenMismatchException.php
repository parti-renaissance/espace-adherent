<?php

namespace App\Exception;

use App\Entity\AdherentExpirableTokenInterface;
use Ramsey\Uuid\UuidInterface;

final class AdherentTokenMismatchException extends AdherentTokenException
{
    private $unexpectedAdherentUuid;

    public function __construct(
        AdherentExpirableTokenInterface $token,
        UuidInterface $unexpectedAdherentUuid,
        \Exception $previous = null
    ) {
        $message = sprintf(
            'The %s token %s cannot be used by the adherent %s but only by adherent %s.',
            $token->getType(),
            $token->getValue(),
            $unexpectedAdherentUuid,
            $token->getAdherentUuid()
        );

        parent::__construct($token, $message, $previous);

        $this->unexpectedAdherentUuid = $unexpectedAdherentUuid;
    }

    public function getUnexpectedAdherentUuid(): UuidInterface
    {
        return $this->unexpectedAdherentUuid;
    }
}
