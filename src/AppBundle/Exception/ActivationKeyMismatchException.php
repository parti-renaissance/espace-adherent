<?php

namespace AppBundle\Exception;

use AppBundle\Entity\ActivationKey;
use Ramsey\Uuid\UuidInterface;

final class ActivationKeyMismatchException extends ActivationKeyException
{
    private $unexpectedAdherentUuid;

    public function __construct(ActivationKey $key, UuidInterface $unexpectedAdherentUuid, \Exception $previous = null)
    {
        $message = sprintf(
            'Activation key %s cannot be used by the adherent %s but only by adherent %s.',
            $key->getToken(),
            $unexpectedAdherentUuid,
            $key->getAdherentUuid()
        );

        parent::__construct($key, $message, $previous);

        $this->unexpectedAdherentUuid = $unexpectedAdherentUuid;
    }

    public function getUnexpectedAdherentUuid(): UuidInterface
    {
        return $this->unexpectedAdherentUuid;
    }
}
