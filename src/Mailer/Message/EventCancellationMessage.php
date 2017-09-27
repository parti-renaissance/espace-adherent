<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
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
     * @param \Closure   $recipientVarsGenerator
     *
     * @return EventCancellationMessage
     */
    public static function create(
        array $recipients,
        Adherent $host,
        BaseEvent $event,
        string $eventsLink,
        \Closure $recipientVarsGenerator
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one Adherent recipients is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', Adherent::class, get_class($recipient)));
        }

        $message = new self(
            Uuid::uuid4(),
            '78678',
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            sprintf('L\'événement "%s" a été annulé.', $event->getName()),
            static::getTemplateVars($event->getName(), $eventsLink),
            $recipientVarsGenerator($recipient),
            $host->getEmailAddress()
        );

        /* @var Adherent[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                $recipientVarsGenerator($recipient)
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

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
