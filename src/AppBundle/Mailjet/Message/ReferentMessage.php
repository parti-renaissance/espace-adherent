<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Referent\ManagedUser;
use AppBundle\Referent\ReferentMessage as ReferentMessageModel;
use Ramsey\Uuid\Uuid;

final class ReferentMessage extends MailjetMessage
{
    public static function createFromModel(ReferentMessageModel $model): self
    {
        $recipients = $model->getTo();

        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        /** @var ManagedUser $recipient */
        $recipient = array_shift($recipients);
        if (!$recipient instanceof ManagedUser) {
            throw new \RuntimeException('First recipient must be an ManagedUser instance.');
        }

        $batchUuid = Uuid::uuid4();

        $message = new self(
            Uuid::uuid4(),
            '63336',
            $recipient->getEmail(),
            $recipient->getFullName(),
            $model->getSubject(),
            [
                'referant_firstname' => self::escape($model->getFrom()->getFullName()),
                'target_message' => nl2br(self::escape($model->getContent())),
            ],
            [
                'target_firstname' => self::escape($recipient->getFullName() ?: ''),
            ],
            $model->getFrom()->getEmailAddress(),
            $batchUuid
        );

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmail(),
                $recipient->getFullName(),
                [
                    'target_firstname' => self::escape($recipient->getFullName() ?: ''),
                ]
            );
        }

        return $message;
    }
}
