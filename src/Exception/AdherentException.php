<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Uid\Uuid;

class AdherentException extends \RuntimeException
{
    private $adherentUuid;

    public function __construct(Uuid $adherentUuid, $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->adherentUuid = $adherentUuid;
    }

    public function getAdherentUuid(): Uuid
    {
        return $this->adherentUuid;
    }
}
