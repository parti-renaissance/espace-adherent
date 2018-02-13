<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Referent\ReferentMessage as ReferentMessageModel;
use Ramsey\Uuid\Uuid;

final class ReferentMessage extends Message
{
    /**
     * @param ReferentMessageModel  $model
     * @param ReferentManagedUser[] $recipients
     *
     * @return ReferentMessage
     */
    public static function createFromModel(ReferentMessageModel $model, array $recipients): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $referent = $model->getFrom();
        $recipient = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmail(),
            $recipient->getFullName() ?: '',
            self::getTemplateVars($referent->getFirstName(), $model->getContent()),
            self::getRecipientVars($recipient->getFirstName() ?: ''),
            $referent->getEmailAddress()
        );

        $message->setSenderName(sprintf('Votre référent%s En Marche !', $referent->isFemale() ? 'e' : ''));

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmail(),
                $recipient->getFullName() ?: '',
                self::getRecipientVars($recipient->getFirstName() ?: '')
            );
        }

        return $message;
    }

    private static function getTemplateVars(string $referentFirstName, string $targetMessage): array
    {
        return [
            'referent_firstname' => self::escape($referentFirstName),
            'target_message' => $targetMessage,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'target_firstname' => self::escape($firstName),
        ];
    }
}
