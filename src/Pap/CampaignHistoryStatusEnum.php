<?php

namespace App\Pap;

use MyCLabs\Enum\Enum;

class CampaignHistoryStatusEnum extends Enum
{
    public const DOOR_OPEN = 'door_open';
    public const DOOR_CLOSED = 'door_closed';
    public const ACCEPT_TO_ANSWER = 'accept_to_answer';
    public const DONT_ACCEPT_TO_ANSWER = 'dont_accept_to_answer';
    public const CONTACT_LATER = 'contact_later';

    public const LABELS = [
        self::DOOR_OPEN => 'Porte ouverte',
        self::DOOR_CLOSED => 'Porte fermée',
        self::ACCEPT_TO_ANSWER => 'Accepte de répondre aux questions',
        self::DONT_ACCEPT_TO_ANSWER => 'N\'accepte pas',
        self::CONTACT_LATER => 'Repasser plus tard',
    ];

    public const DOOR_STATUS = [
        self::DOOR_CLOSED,
        self::DOOR_OPEN,
    ];

    public const RESPONSE_STATUS = [
        self::ACCEPT_TO_ANSWER,
        self::DONT_ACCEPT_TO_ANSWER,
        self::CONTACT_LATER,
    ];

    public const FINISHED_STATUS = [
        self::DOOR_CLOSED,
        self::ACCEPT_TO_ANSWER,
        self::DONT_ACCEPT_TO_ANSWER,
        self::CONTACT_LATER,
    ];

    public const ALL = [
        self::DOOR_OPEN,
        self::DOOR_CLOSED,
        self::ACCEPT_TO_ANSWER,
        self::DONT_ACCEPT_TO_ANSWER,
        self::CONTACT_LATER,
    ];
}
