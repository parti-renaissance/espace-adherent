<?php

namespace App\Mailer\Message\Coalition;

use App\Entity\Coalition\CauseFollower;
use App\Entity\Event\CauseEvent;
use Ramsey\Uuid\Uuid;

final class CauseEventCreationMessage extends AbstractCoalitionMessage
{
    public static function create(array $recipients, CauseEvent $event, string $eventLink, string $causeLink): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof CauseFollower) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', CauseFollower::class, \get_class($recipient)));
        }

        $adherent = $recipient->getAdherent();
        $message = new self(
            Uuid::uuid4(),
            $adherent ? $adherent->getEmailAddress() : $recipient->getEmailAddress(),
            $adherent ? $adherent->getFirstName() : $recipient->getFirstName(),
            '✊ Nouvel événement sur une cause que vous soutenez',
            static::getTemplateVars($event, $eventLink, $causeLink),
            static::getRecipientVars($recipient)
        );

        /* @var CauseFollower[] $recipients */
        foreach ($recipients as $recipient) {
            $adherent = $recipient->getAdherent();
            $message->addRecipient(
                $adherent ? $adherent->getEmailAddress() : $recipient->getEmailAddress(),
                $adherent ? $adherent->getFirstName() : $recipient->getFirstName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(CauseEvent $event, string $eventLink, string $causeLink): array
    {
        return [
            'cause_name' => self::escape($event->getCause()->getName()),
            'cause_link' => $causeLink,
            'event_name' => self::escape($event->getName()),
            'event_description' => self::escape($event->getName()),
            'event_link' => $eventLink,
            'event_date' => static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
            'event_hour' => sprintf(
                '%sh%s',
                static::formatDate($event->getLocalBeginAt(), 'HH'),
                static::formatDate($event->getLocalBeginAt(), 'mm')
            ),
            'event_address' => $event->getInlineFormattedAddress(),
            'event_visio_url' => $event->getVisioUrl(),
        ];
    }

    private static function getRecipientVars(CauseFollower $recipient): array
    {
        return [
            'first_name' => self::escape(
                $recipient->isAdherent() ? $recipient->getAdherent()->getFirstName() : $recipient->getFirstName()
            ),
        ];
    }
}
