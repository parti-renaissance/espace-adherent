<?php

declare(strict_types=1);

namespace App\VotingPlatform\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class NotifyPartialElectionVoterCommand implements AsynchronousMessageInterface
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
