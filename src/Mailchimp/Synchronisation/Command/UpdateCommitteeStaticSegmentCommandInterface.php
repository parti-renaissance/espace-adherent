<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Messenger\Message\AsyncMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface UpdateCommitteeStaticSegmentCommandInterface extends AsyncMessageInterface
{
    public function getAdherentUuid(): UuidInterface;

    public function getCommitteeUuid(): UuidInterface;
}
