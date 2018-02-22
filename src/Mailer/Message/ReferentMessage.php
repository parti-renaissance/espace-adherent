<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Referent\ReferentMessage as ReferentMessageModel;
use Ramsey\Uuid\Uuid;

final class ReferentMessage extends Message
{
    public static function create(ReferentMessageModel $model, array $recipients): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof ReferentManagedUser) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', ReferentManagedUser::class));
        }

        $referent = $model->getFrom();

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmail(),
            $recipient->getFullName() ?: '',
            static::getTemplateVars($referent, $model->getContent()),
            static::getRecipientVars($recipient),
            $referent->getEmailAddress()
        );

        $message->setSenderName(sprintf('Votre référent%s En Marche !', $referent->isFemale() ? 'e' : ''));

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof ReferentManagedUser) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', ReferentManagedUser::class));
            }

            $message->addRecipient(
                $recipient->getEmail(),
                $recipient->getFullName() ?: '',
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(Adherent $referent, string $message): array
    {
        return [
            'referent_first_name' => self::escape($referent->getFirstName()),
            'message' => $message,
        ];
    }

    private static function getRecipientVars(ReferentManagedUser $recipient): array
    {
        return [
            'first_name' => self::escape($recipient->getFirstName() ?: ''),
        ];
    }
}
