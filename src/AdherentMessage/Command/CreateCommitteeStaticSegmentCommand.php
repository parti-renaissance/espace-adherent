<?php

namespace AppBundle\AdherentMessage\Command;

use AppBundle\Messenger\Message\AsyncMessageInterface;
use Ramsey\Uuid\UuidInterface;

class CreateCommitteeStaticSegmentCommand implements AsyncMessageInterface
{
    private $committeeUuid;

    public function __construct(UuidInterface $committeeUuid)
    {
        $this->committeeUuid = $committeeUuid;
    }

    public function getCommitteeUuid(): UuidInterface
    {
        return $this->committeeUuid;
    }
}
