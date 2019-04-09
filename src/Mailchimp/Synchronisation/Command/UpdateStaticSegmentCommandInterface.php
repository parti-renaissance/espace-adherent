<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Messenger\Message\AsyncMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface UpdateStaticSegmentCommandInterface extends AsyncMessageInterface
{
    public function getAdherentUuid(): UuidInterface;

    public function getObjectUuid(): UuidInterface;

    public function getEntityClass(): string;
}
