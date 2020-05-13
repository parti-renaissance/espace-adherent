<?php

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface UpdateStaticSegmentCommandInterface extends SynchronizeMessageInterface
{
    public function getAdherentUuid(): UuidInterface;

    public function getObjectUuid(): UuidInterface;

    public function getEntityClass(): string;
}
