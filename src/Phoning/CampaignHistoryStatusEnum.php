<?php

declare(strict_types=1);

namespace App\Phoning;

use MyCLabs\Enum\Enum;

class CampaignHistoryStatusEnum extends Enum
{
    public const SEND = 'send';
    public const ANSWERED = 'answered'; // not a real status, used only for labels in front
    public const TO_UNSUBSCRIBE = 'to-unsubscribe';
    public const TO_UNJOIN = 'to-unjoin';
    public const NOT_RESPOND = 'not-respond';
    public const TO_REMIND = 'to-remind';
    public const FAILED = 'failed';
    public const INTERRUPTED_DONT_REMIND = 'interrupted-dont-remind';
    public const INTERRUPTED = 'interrupted';
    public const COMPLETED = 'completed';

    public const LABELS = [
        self::SEND => 'Numéro de téléphone envoyé',
        self::ANSWERED => 'Accepte de répondre aux questions',
        self::TO_UNSUBSCRIBE => 'Ne souhaite plus être rappelé',
        self::TO_UNJOIN => 'Souhaite désadhérer',
        self::NOT_RESPOND => 'N\'a pas répondu au téléphone',
        self::TO_REMIND => 'Souhaite être rappelé plus tard',
        self::FAILED => 'L\'appel a échoué',
        self::INTERRUPTED_DONT_REMIND => 'Appel interrompu, ne pas rappeler',
        self::INTERRUPTED => 'Appel interrompu',
        self::COMPLETED => 'Complété',
    ];

    public const AFTER_CALL_STATUS = [
        self::TO_UNSUBSCRIBE,
        self::TO_UNJOIN,
        self::NOT_RESPOND,
        self::TO_REMIND,
        self::FAILED,
        self::INTERRUPTED_DONT_REMIND,
        self::INTERRUPTED,
        self::COMPLETED,
    ];

    public const FINISHED_STATUS = [
        self::ANSWERED,
        self::TO_UNSUBSCRIBE,
        self::TO_UNJOIN,
        self::NOT_RESPOND,
        self::TO_REMIND,
        self::FAILED,
    ];

    public const INTERRUPTED_STATUS = [
        self::INTERRUPTED_DONT_REMIND,
        self::INTERRUPTED,
    ];

    public const NOT_CALLABLE = [
        self::TO_UNJOIN,
        self::TO_UNSUBSCRIBE,
        self::FAILED,
    ];

    public const CALLABLE_LATER = [
        self::SEND,
        self::NOT_RESPOND,
        self::TO_REMIND,
        self::INTERRUPTED,
    ];
}
