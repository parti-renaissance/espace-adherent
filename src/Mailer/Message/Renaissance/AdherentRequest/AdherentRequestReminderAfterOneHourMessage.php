<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\AdherentRequest;

class AdherentRequestReminderAfterOneHourMessage extends AbstractAdherentRequestReminderMessage
{
    protected static function getReminderSubject(): string
    {
        return 'Terminez votre inscription! (1/3)';
    }
}
