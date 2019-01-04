<?php

namespace AppBundle\Mailchimp\Synchronisation\Message;

use AppBundle\Messenger\Message\AsyncMessageInterface;
use Ramsey\Uuid\UuidInterface;

interface AdherentMessageInterface extends AsyncMessageInterface
{
    public function getUuid(): UuidInterface;

    public function getEmailAddress(): string;

    public function getRemovedTags(): array;
}
