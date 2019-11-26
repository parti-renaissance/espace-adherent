<?php

namespace AppBundle\Adherent\Command;

use AppBundle\Messenger\Message\AsynchronousMessageInterface;

class UpdateReferentTagOnDistrictCommand implements AsynchronousMessageInterface
{
    private $adherentId;

    public function __construct(int $adherentId)
    {
        $this->adherentId = $adherentId;
    }

    public function getAdherentId(): int
    {
        return $this->adherentId;
    }
}
