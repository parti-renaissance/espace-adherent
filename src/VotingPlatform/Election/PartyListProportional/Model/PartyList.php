<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election\PartyListProportional\Model;

class PartyList
{
    private int $seats = 0;

    public function __construct(public string $identifier, public int $totalVotes)
    {
    }

    public function getSeats(): int
    {
        return $this->seats;
    }

    public function increaseSeats(int $step = 1): void
    {
        $this->seats += $step;
    }
}
