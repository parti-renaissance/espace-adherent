<?php

namespace App\VotingPlatform\Election\PartyListProportional;

use App\VotingPlatform\Election\PartyListProportional\Model\Election;
use App\VotingPlatform\Election\PartyListProportional\Model\PartyList;

class Processor
{
    private const FIRST_COEFFICIENT = 1.4;

    public static function process(Election $election): void
    {
        while ($election->hasFreeSeats()) {
            $quotients = [];

            foreach ($election->partyLists as $list) {
                $quotients[$list->identifier] = static::calculateQuotient($list);
            }

            arsort($quotients, \SORT_NUMERIC);

            if (
                \count($quotients) > 1
                && array_values($quotients)[0] === array_values($quotients)[1]
                && $election->getFreeSeatsNumber() <= 2
            ) {
                return;
            }

            $list = $election->findListByIdentifier(key($quotients));
            $list->increaseSeats();
        }
    }

    private static function calculateQuotient(PartyList $list): float
    {
        $divider = 2 * $list->getSeats() + 1;

        if (1 === $divider) {
            $divider = self::FIRST_COEFFICIENT;
        }

        return $list->totalVotes / $divider;
    }
}
