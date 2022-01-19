<?php

namespace App\Serializer\Encoder;

use Sabre\VObject\Component\VCalendar;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class ICalEncoder implements EncoderInterface
{
    public const FORMAT = 'ical';

    public function encode($data, $format, array $context = [])
    {
        return (new VCalendar($data))->serialize();
    }

    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }
}
