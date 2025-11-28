<?php

declare(strict_types=1);

namespace App\VotingPlatform\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Ramsey\Uuid\UuidInterface;

class UpdateMandateForElectedAdherentCommand implements AsynchronousMessageInterface
{
    private $electionUuid;

    public function __construct(UuidInterface $electionUuid)
    {
        $this->electionUuid = $electionUuid;
    }

    public function getElectionUuid(): UuidInterface
    {
        return $this->electionUuid;
    }
}
