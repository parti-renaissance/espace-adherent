<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;
use Symfony\Component\Uid\Uuid;

interface AdherentChangeCommandInterface extends SynchronizeMessageInterface
{
    public function getUuid(): Uuid;

    public function getEmailAddress(): string;

    public function getRemovedTags(): array;
}
