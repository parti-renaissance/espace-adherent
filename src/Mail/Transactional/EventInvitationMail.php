<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class EventInvitationMail extends TransactionalMail
{
    private const SUBJECT_PATTERN = '%s vous invite à un événement En Marche !';

    public static function createRecipient(EventInvite $invite): RecipientInterface
    {
        return new Recipient($invite->getEmail(), StringCleaner::htmlspecialchars($invite->getFullName()));
    }

    public static function createTemplateVars(EventInvite $invite, Event $event, string $eventUrl): array
    {
        return [
            'sender_firstname' => StringCleaner::htmlspecialchars($invite->getFirstName()),
            'sender_message' => StringCleaner::htmlspecialchars($invite->getMessage()),
            'event_name' => StringCleaner::htmlspecialchars($event->getName()),
            'event_slug' => $eventUrl,
        ];
    }

    public static function createSubject(EventInvite $invite): string
    {
        return sprintf(self::SUBJECT_PATTERN, $invite->getFullName());
    }

    public static function createReplyTo(EventInvite $invite): RecipientInterface
    {
        return new Recipient($invite->getEmail());
    }

    public static function createCcRecipients(array $emails): array
    {
        return array_map(function (string $email) {
            return new Recipient($email);
        }, $emails);
    }
}
