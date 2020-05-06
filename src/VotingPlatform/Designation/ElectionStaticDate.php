<?php

namespace AppBundle\VotingPlatform\Designation;

class ElectionStaticDate
{
    public static function getCandidacyPeriodStartDate(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('d/m/Y H:i:s', '01/06/2020 08:00:00');
    }

    public static function getCandidacyPeriodEndDate(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('d/m/Y H:i:s', '22/06/2020 00:00:00');
    }

    public static function getVoteStartDate(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('d/m/Y H:i:s', '22/06/2020 08:00:00');
    }

    public static function getVoteEndDate(): \DateTimeInterface
    {
        return \DateTime::createFromFormat('d/m/Y H:i:s', '05/07/2020 20:00:00');
    }
}
