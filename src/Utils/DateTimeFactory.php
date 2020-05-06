<?php

namespace App\Utils;

abstract class DateTimeFactory
{
    public static function create(string $dateTime, string $format = 'Y-m-d H:i:s'): \DateTime
    {
        if (false === $object = \DateTime::createFromFormat($format, $dateTime)) {
            throw new \InvalidArgumentException(sprintf('Cannot create DateTime object from "%s" string with "%s" format', $dateTime, $format));
        }

        return $object;
    }
}
