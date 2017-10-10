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
        $first = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmail(),
            $first->getFullName() ?: '',
            [
                'referant_firstname' => self::escape($referent->getFirstName()),
                'target_message' => $model->getContent(),
            ],
            [
                'target_firstname' => self::escape($first->getFirstName() ?: ''),
            ],
            $referent->getEmailAddress()
        );

        $message->setSenderName(sprintf('Votre référent%s En Marche !', $referent->isFemale() ? 'e' : ''));

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmail(),
                $recipient->getFullName() ?: '',
                [
                    'target_firstname' => self::escape($recipient->getFirstName() ?: ''),
                ]
            );
        }

        return $message;
    }
}
