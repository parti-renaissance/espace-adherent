<?php

namespace App\Mailer\Message\Renaissance\AdherentRequest;

class AdherentRequestReminderNextSaturdayMessage extends AbstractAdherentRequestReminderMessage
{
    protected static function getReminderSubject(): string
    {
        return 'Terminez votre inscription! (2/3)';
    }
}
