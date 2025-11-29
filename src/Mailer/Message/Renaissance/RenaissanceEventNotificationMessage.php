<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use Ramsey\Uuid\Uuid;

final class RenaissanceEventNotificationMessage extends AbstractRenaissanceMessage
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[] $recipients
     */
    public static function create(array $recipients, Adherent $host, Event $event, string $eventLink): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $vars = [
            // Global common variables
            'animator_firstname' => self::escape($host->getFirstName()),
            'event_name' => self::escape($event->getName()),
            'event_date' => static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
            'event_hour' => static::formatDate($event->getLocalBeginAt(), 'HH\'h\'mm'),
            'event_address' => self::escape($event->getInlineFormattedAddress()),
            'event_slug' => $eventLink,
            'event_description' => $event->getDescription(),
            'committee_name' => $event->getCommittee()?->getName(),
            'visio_url' => $event->getVisioUrl(),
            'live_url' => $event->liveUrl,
        ];

        $message = new static(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            \sprintf(
                '%s - %s : Nouvel événement%s : %s',
                static::formatDate($event->getLocalBeginAt(), 'd MMMM'),
                $vars['event_hour'],
                ($committeeName = $event->getCommittee()?->getName()) ? ' de '.$committeeName : '',
                $vars['event_name']
            ),
            $vars,
            static::getRecipientVars($recipient->getFirstName()),
            $host->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient->getFirstName())
            );
        }

        return $message;
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
