<?php

namespace AppBundle\Donation;

class Donors
{
    const SPONSORS = 'mécènes';
    const AMBASSADORS = 'ambassadeurs';
    const BENEFACTORS = 'bienfaiteurs';
    const SUPPORTS = 'soutiens';

    public static function getProfile(float $amount)
    {
        if ($amount >= 5000) {
            return self::SPONSORS;
        }

        if ($amount >= 500) {
            return self::AMBASSADORS;
        }

        if ($amount >= 50) {
            return self::BENEFACTORS;
        }

        return self::SUPPORTS;
    }
}
