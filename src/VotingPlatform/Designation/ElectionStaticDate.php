<?php

namespace AppBundle\VotingPlatform\Designation;

class ElectionStaticDate
{
    public static function getCandidacyPeriodEndDate(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('d/m/Y H:i:s', '25/05/2020 00:00:00');
    }

    public static function getVoteStartDate(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('d/m/Y H:i:s', '25/05/2020 08:00:00');
    }

    public static function getVoteEndDate(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('d/m/Y H:i:s', '07/06/2020 20:00:00');
    }
}
