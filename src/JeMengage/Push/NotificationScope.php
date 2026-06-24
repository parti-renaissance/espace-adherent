<?php

declare(strict_types=1);

namespace App\JeMengage\Push;

class NotificationScope
{
    public const PREFIX_NATIONAL = 'national';
    public const PREFIX_ZONE = 'zone:';
    public const PREFIX_COMMITTEE = 'committee:';
    public const PREFIX_EVENT = 'event:';
    public const PREFIX_ACTION = 'action:';
    public const PREFIX_MEETING = 'meeting:';
    public const PREFIX_PRIVATE_MESSAGE = 'private_message:';
    public const PREFIX_PUBLICATION = 'publication:';
    public const PREFIX_PRONOSTIC_PARTICIPANTS = 'pronostic_participants:';

    public static function national(): string
    {
        return self::PREFIX_NATIONAL;
    }

    public static function zone(string $zoneCode): string
    {
        return self::PREFIX_ZONE.$zoneCode;
    }

    public static function committee(int $committeeId): string
    {
        return self::PREFIX_COMMITTEE.$committeeId;
    }

    public static function event(int $eventId): string
    {
        return self::PREFIX_EVENT.$eventId;
    }

    public static function action(int $actionId): string
    {
        return self::PREFIX_ACTION.$actionId;
    }

    public static function meeting(int $meetingId): string
    {
        return self::PREFIX_MEETING.$meetingId;
    }

    public static function privateMessage(int $messageId): string
    {
        return self::PREFIX_PRIVATE_MESSAGE.$messageId;
    }

    public static function publication(int $publicationId): string
    {
        return self::PREFIX_PUBLICATION.$publicationId;
    }

    public static function pronosticParticipants(int $pronosticId): string
    {
        return self::PREFIX_PRONOSTIC_PARTICIPANTS.$pronosticId;
    }
}
