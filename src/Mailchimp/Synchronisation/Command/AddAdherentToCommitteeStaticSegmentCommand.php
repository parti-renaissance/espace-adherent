<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use Ramsey\Uuid\UuidInterface;

class AddAdherentToCommitteeStaticSegmentCommand implements UpdateCommitteeStaticSegmentCommandInterface
{
    private $adherentUuid;
    private $committeeUuid;

    public function __construct(UuidInterface $adherentUuid, UuidInterface $committeeUuid)
    {
        $this->adherentUuid = $adherentUuid;
        $this->committeeUuid = $committeeUuid;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    public function getCommitteeUuid(): UuidInterface
    {
        return $this->committeeUuid;
    }
}
