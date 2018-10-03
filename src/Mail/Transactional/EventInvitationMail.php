<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class EventInvitationMail extends TransactionalMail
{
    const SUBJECT = '%s vous invite à un événement En Marche !';

    public static function createRecipientFor(EventInvite $invite): RecipientInterface
    {
        return new Recipient($invite->getEmail(), StringCleaner::htmlspecialchars($invite->getFullName()));
    }

    public static function createTemplateVarsFrom(EventInvite $invite, Event $event, string $eventUrl): array
    {
        return [
            'sender_firstname' => StringCleaner::htmlspecialchars($invite->getFirstName()),
            'sender_message' => StringCleaner::htmlspecialchars($invite->getMessage()),
            'event_name' => StringCleaner::htmlspecialchars($event->getName()),
            'event_slug' => $eventUrl,
        ];
    }

    public static function createSubjectFor(EventInvite $invite): string
    {
        return sprintf(self::SUBJECT, $invite->getFullName());
    }
}
