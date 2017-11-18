<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class EventCancellationMessage extends Message
{
    /**
     * Creates a new message instance for a list of recipients.
     *
     * @param Adherent[] $recipients
     * @param Adherent   $host
     * @param BaseEvent  $event
     * @param string     $eventsLink
     *
     * @return EventCancellationMessage
     */
    public static function create(
        array $recipients,
        Adherent $host,
        BaseEvent $event,
        string $eventsLink
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', EventRegistration::class, get_class($recipient)));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            self::getTemplateVars($event->getName(), $eventsLink),
            self::getRecipientVars($recipient->getFirstName()),
            $host->getEmailAddress()
        );

        /* @var Adherent[] $recipients */
        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException('This message builder requires a collection of Adherent instances');
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFirstName().' '.$recipient->getLastName(),
                self::getRecipientVars($recipient->getFirstName())
            );
        }

        return $message;
    }

    private static function getTemplateVars(string $eventName, string $eventsLink): array
    {
        return [
            'event_name' => $eventName,
            'event_slug' => $eventsLink,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
