<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\InstitutionalEvent;
use Ramsey\Uuid\Uuid;

final class InstitutionalEventInvitationMessage extends Message
{
    public static function createFromInstitutionalEvent(InstitutionalEvent $institutionalEvent): self
    {
        $referent = $institutionalEvent->getOrganizer();

        $message = new self(
            Uuid::uuid4(),
            '704914',
            $referent->getEmailAddress(),
            self::escape($referent->getFullName()),
            '',
            [
                'institutional_event_name' => self::escape($institutionalEvent->getName()),
                'institutional_event_type' => self::escape($institutionalEvent->getCategoryName()),
                'institutional_event_starting_day' => self::formatDate(
                    $institutionalEvent->getLocalBeginAt(), 'EEEE d MMMM y'
                ),
                'institutional_event_starting_time' => sprintf(
                    '%sh%s',
                    static::formatDate($institutionalEvent->getLocalBeginAt(), 'HH'),
                    static::formatDate($institutionalEvent->getLocalBeginAt(), 'mm')
                ),
                'institutional_event_address' => self::escape($institutionalEvent->getInlineFormattedAddress()),
                'sender_email' => self::escape($referent->getEmailAddress()),
                'sender_name' => self::escape($referent->getFullName()),
                'sender_description' => self::escape($institutionalEvent->getDescription()),
            ]
        );

        $message->setReplyTo($referent->getEmailAddress());

        foreach ($institutionalEvent->getInvitations() as $invitationEmail) {
            $message->addBCC($invitationEmail);
        }

        return $message;
    }

    private static function formatDate(\DateTimeInterface $date, string $format): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            $format
        );

        return $formatter->format($date);
    }
}
