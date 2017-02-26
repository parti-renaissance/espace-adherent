<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Referent\ReferentMessage as ReferentMessageModel;
use Ramsey\Uuid\Uuid;

final class ReferentMessage extends MailjetMessage
{
    public static function createFromModel(ReferentMessageModel $model): self
    {
        $referent = $model->getFrom();
        $recipients = $model->getTo();

        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $batchUuid = Uuid::uuid4();

        $message = new self(
            Uuid::uuid4(),
            '63336',
            $referent->getEmailAddress(),
            $referent->getFullName(),
            $model->getSubject(),
            [
                'referant_firstname' => self::escape($referent->getFirstName()),
                'target_message' => $model->getContent(),
            ],
            [
                'target_firstname' => self::escape($referent->getFirstName() ?: ''),
            ],
            $referent->getEmailAddress(),
            $batchUuid
        );

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmail(),
                $recipient->getFullName(),
                [
                    'target_firstname' => self::escape($recipient->getFirstName() ?: ''),
                ]
            );
        }

        return $message;
    }
}
