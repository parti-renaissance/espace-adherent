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

            if ($list = static::findWinnerList($election, $quotients)) {
                $list->increaseSeats();
            }
        }
    }

    private static function calculateQuotient(PartyList $list): float
    {
        $divider = 2 * $list->getSeats() + 1;

        if (1 === $divider && 0 === $list->getSeats()) {
            $divider = self::FIRST_COEFFICIENT;
        }

        return $list->totalVotes / $divider;
    }

    private static function findWinnerList(Election $election, array $quotients): ?PartyList
    {
        $max = max($quotients);

        $winnerListIds = [];
        foreach ($quotients as $listIdentifier => $quotient) {
            if ($quotient === $max) {
                $winnerListIds[] = (string) $listIdentifier;
            }
        }

        $winnerList = null;

        if (\count($winnerListIds) > 0) {
            $maxVote = 0;
            foreach ($winnerListIds as $listId) {
                $list = $election->findListByIdentifier($listId);
                if ($list && $list->totalVotes > $maxVote) {
                    $maxVote = $list->totalVotes;
                    $winnerList = $list;
                }
            }
        } else {
            $winnerList = $election->findListByIdentifier(current($winnerListIds));
        }

        return $winnerList;
    }
}
