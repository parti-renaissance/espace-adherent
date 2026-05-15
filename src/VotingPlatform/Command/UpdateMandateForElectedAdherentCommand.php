<?php

declare(strict_types=1);

namespace App\VotingPlatform\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Symfony\Component\Uid\Uuid;

class UpdateMandateForElectedAdherentCommand implements AsynchronousMessageInterface
{
    private $electionUuid;

    public function __construct(Uuid $electionUuid)
    {
        $this->electionUuid = $electionUuid;
    }

    public function getElectionUuid(): Uuid
    {
        return $this->electionUuid;
    }
}
