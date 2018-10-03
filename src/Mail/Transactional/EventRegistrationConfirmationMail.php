<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Entity\Invite;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class EventRegistrationConfirmationMail extends TransactionalMail
{
    const SUBJECT = 'Confirmation de participation à un événement En Marche !';

    public static function createRecipientFor(EventRegistration $registration): RecipientInterface
    {
        return new Recipient($registration->getEmailAddress(), $registration->getFirstName(), [
            'prenom' => StringCleaner::htmlspecialchars($registration->getFirstName()),
        ]);
    }

    public static function createTemplateVarsFrom(Event $event, string $eventLink): array
    {
        return [
            'event_name' => StringCleaner::htmlspecialchars($event->getName()),
            'event_organiser' => StringCleaner::htmlspecialchars($event->getOrganizerName()),
            'event_link' => $eventLink,
        ];
    }
}
