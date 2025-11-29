<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election\Event;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use Symfony\Contracts\EventDispatcher\Event;

class NewVote extends Event
{
    public function __construct(
        public readonly Election $election,
        public readonly Voter $voter,
        public readonly string $voterKey,
    ) {
    }
}
