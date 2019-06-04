<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use AppBundle\Mailchimp\SynchronizeMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface UpdateStaticSegmentCommandInterface extends SynchronizeMessageInterface
{
    public function getAdherentUuid(): UuidInterface;

    public function getObjectUuid(): UuidInterface;

    public function getEntityClass(): string;
}
