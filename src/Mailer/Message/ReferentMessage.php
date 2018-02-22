<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Referent\ReferentMessage as ReferentMessageModel;
use Ramsey\Uuid\Uuid;

final class ReferentMessage extends Message
{
    public static function createFromModel(ReferentMessageModel $model, array $recipients): self
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
            'referent_firstname' => self::escape($referent->getFirstName()),
            'target_message' => $message,
        ];
    }

    private static function getRecipientVars(ReferentManagedUser $recipient): array
    {
        return [
            'target_firstname' => self::escape($recipient->getFirstName() ?: ''),
        ];
    }
}
