<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Collection\EventRegistrationCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class EventCancellationMail extends TransactionalMail
{
    private const SUBJECT_PATTERN = 'L\'événement "%s" a été annulé.';

    public static function createRecipients(EventRegistrationCollection $registrations): array
    {
        return $registrations
            ->map(function (EventRegistration $registration) {
                return new Recipient(
                    $registration->getEmailAddress(),
                    $registration->getFullName(),
                    ['target_firstname' => StringCleaner::htmlspecialchars($registration->getFirstName())]
                );
            })
            ->toArray()
        ;
    }

    public static function createTemplateVars(Event $event, string $eventsLink): array
    {
        return [
            'event_name' => $event->getName(),
            'event_slug' => $eventsLink,
        ];
    }

    public static function createSubject(Event $event): string
    {
        return sprintf(self::SUBJECT_PATTERN, $event->getName());
    }

    public static function createReplyTo(Adherent $host): RecipientInterface
    {
        return new Recipient($host->getEmailAddress());
    }
}
