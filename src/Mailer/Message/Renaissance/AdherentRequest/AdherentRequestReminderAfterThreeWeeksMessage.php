<?php

namespace App\Mailer\Message\Renaissance\AdherentRequest;

class AdherentRequestReminderAfterThreeWeeksMessage extends AbstractAdherentRequestReminderMessage
{
    protected static function getReminderSubject(): string
    {
        return 'Terminez votre inscription! (3/3)';
    }
}
