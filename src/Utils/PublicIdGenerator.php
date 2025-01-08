<?php

namespace App\Utils;

use Ramsey\Uuid\UuidInterface;

class PublicIdGenerator
{
    private const string ALLOWED_CHARACTERS = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    private const int SEGMENT_LENGTH = 3;

    public static function generatePublicIdFromUuid(UuidInterface $uuid): string
    {
        $hash = md5($uuid->toString());
        $segment1 = '';
        $segment2 = '';

        for ($i = 0; $i < self::SEGMENT_LENGTH; ++$i) {
            $segment1 .= self::convertHexToAllowedCharacter($hash[$i]);
            $segment2 .= self::convertHexToAllowedCharacter($hash[$i + self::SEGMENT_LENGTH]);
        }

        return "$segment1-$segment2";
    }

    private static function convertHexToAllowedCharacter(string $hexCharacter): string
    {
        return self::ALLOWED_CHARACTERS[hexdec($hexCharacter) % \strlen(self::ALLOWED_CHARACTERS)];
    }
}
