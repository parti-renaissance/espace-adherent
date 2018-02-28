<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenActionCancellationMessage extends Message
{
    public static function create(
        array $recipients,
        Adherent $author,
        CitizenAction $citizenAction,
        string $eventsLink
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', EventRegistration::class));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($citizenAction, $eventsLink),
            static::getRecipientVars($recipient),
            $author->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof EventRegistration) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', EventRegistration::class));
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(CitizenAction $citizenAction, string $eventsLink): array
    {
        return [
            'citizen_action_name' => self::escape($citizenAction->getName()),
            'events_link' => $eventsLink,
        ];
    }

    private static function getRecipientVars(EventRegistration $recipient): array
    {
        return [
            'first_name' => self::escape($recipient->getFirstName()),
        ];
    }
}
