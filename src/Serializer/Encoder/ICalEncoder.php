<?php

declare(strict_types=1);

namespace App\Serializer\Encoder;

use Sabre\VObject\Component\VCalendar;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class ICalEncoder implements EncoderInterface
{
    public const FORMAT = 'ical';

    public function encode($data, $format, array $context = []): string
    {
        return new VCalendar($data)->serialize();
    }

    public function supportsEncoding($format): bool
    {
        return self::FORMAT === $format;
    }
}
