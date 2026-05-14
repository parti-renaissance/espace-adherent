<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;
use Symfony\Component\Uid\Uuid;

interface UpdateStaticSegmentCommandInterface extends SynchronizeMessageInterface
{
    public function getAdherentUuid(): Uuid;

    public function getObjectUuid(): Uuid;

    public function getEntityClass(): string;
}
