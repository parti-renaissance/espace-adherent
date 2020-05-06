<?php

namespace App\Exception;

use Ramsey\Uuid\UuidInterface;

class AdherentException extends \RuntimeException
{
    private $adherentUuid;

    public function __construct(UuidInterface $adherentUuid, $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->adherentUuid = $adherentUuid;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }
}
