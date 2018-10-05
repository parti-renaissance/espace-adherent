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

class EventCancellationMail extends TransactionalMail
{
    private const SUBJECT = 'L\'événement "%s" a été annulé.';

    public static function createRecipientsFor(EventRegistrationCollection $registrations): RecipientInterface
    {
        return $registrations
            ->map(function (EventRegistration $registration) {
                return new Recipient(
                    $registration->getEmailAddress(),
                    sprintf('%s %s', $registration->getFirstName(), $registration->getLastName()),
                    ['target_firstname' => StringCleaner::htmlspecialchars($registration->getFirstName())]
                );
            })
            ->toArray()
        ;
    }

    public static function createTemplateVarsFrom(Event $event, string $eventsLink): array
    {
        return [
            'event_name' => $event->getName(),
            'event_slug' => $eventsLink,
        ];
    }

    public static function createSubjectFor(Event $event): string
    {
        return sprintf(self::SUBJECT, $event->getName());
    }

    public static function createReplyToFrom(Adherent $host): RecipientInterface
    {
        return new Recipient($host->getEmailAddress());
    }
}
