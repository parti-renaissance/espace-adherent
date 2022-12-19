<?php

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

    public function increaseSeats(): void
    {
        ++$this->seats;
    }
}
