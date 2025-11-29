<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use App\Mailchimp\SynchronizeMessageInterface;

class SyncAllMembersOfCommitteeCommand implements SynchronizeMessageInterface
{
    private $committeeId;

    public function __construct(int $committeeId)
    {
        $this->committeeId = $committeeId;
    }

    public function getCommitteeId(): int
    {
        return $this->committeeId;
    }
}
