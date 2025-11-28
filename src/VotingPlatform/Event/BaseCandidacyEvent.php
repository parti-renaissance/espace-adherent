<?php

declare(strict_types=1);

namespace App\VotingPlatform\Event;

use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BaseCandidacyEvent extends Event
{
    private $candidacy;

    public function __construct(CandidacyInterface $candidacy)
    {
        $this->candidacy = $candidacy;
    }

    public function getCandidacy(): CandidacyInterface
    {
        return $this->candidacy;
    }
}
