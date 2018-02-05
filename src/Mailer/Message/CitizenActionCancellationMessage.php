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
        \Closure $recipientVarsGenerator
    ): self {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \RuntimeException(sprintf('First recipient must be an %s instance, %s given', EventRegistration::class, get_class($recipient)));
        }

        $message = new self(
            Uuid::uuid4(),
            '308754',
            $recipient->getEmailAddress(),
            $recipient->getFirstName().' '.$recipient->getLastName(),
            '[Action citoyenne] Une action citoyenne à laquelle vous participez vient d\'être annulée.',
            static::getTemplateVars($citizenAction->getName()),
            $recipientVarsGenerator($recipient),
            $author->getEmailAddress()
        );

        /* @var Adherent[] $recipients */
        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFirstName().' '.$recipient->getLastName(),
                $recipientVarsGenerator($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(string $citizenActionName): array
    {
        return [
            'citizen_action_name' => self::escape($citizenActionName),
        ];
    }

    public static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
