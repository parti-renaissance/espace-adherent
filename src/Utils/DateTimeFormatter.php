<?php

namespace AppBundle\Utils;

class DateTimeFormatter
{
    public static function formatDate(\DateTimeInterface $date, string $format): string
    {
        return (
            new \IntlDateFormatter(
                'fr_FR',
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE,
                $date->getTimezone(),
                \IntlDateFormatter::GREGORIAN,
                $format
            )
        )->format($date);
    }
}
