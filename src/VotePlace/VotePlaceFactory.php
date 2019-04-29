<?php

namespace AppBundle\VotePlace;

use AppBundle\Entity\VotePlace;

class VotePlaceFactory
{
    public static function createFromArray(array $data): VotePlace
    {
        $votePlace = VotePlace::create(
            $data['name'],
            $data['code'],
            $data['postalCode'] ?? null,
            $data['city'] ?? null,
            $data['address'],
            $data['country'] ?? 'FR'
        );

        return $votePlace;
    }
}
