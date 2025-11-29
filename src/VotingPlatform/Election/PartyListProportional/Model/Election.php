<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election\PartyListProportional\Model;

class Election
{
    /**
     * @param PartyList[] $partyLists
     */
    public function __construct(public readonly int $seats, public array $partyLists)
    {
    }

    public function hasFreeSeats(): bool
    {
        return $this->getFreeSeatsNumber() > 0;
    }

    public function getFreeSeatsNumber(): int
    {
        return $this->seats - $this->calculateTakenSeats();
    }

    private function calculateTakenSeats(): int
    {
        return array_sum(array_map(function (PartyList $list) {
            return $list->getSeats();
        }, $this->partyLists));
    }

    public function findListByIdentifier(string $identifier): ?PartyList
    {
        foreach ($this->partyLists as $list) {
            if ($list->identifier === $identifier) {
                return $list;
            }
        }

        return null;
    }
}
