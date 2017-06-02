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
            self::fixMailjetParsing($referent->getFullName()),
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

        $message->setSenderName(sprintf('Votre référent%s En Marche !', $referent->isFemale() ? 'e' : ''));

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmail(),
                self::fixMailjetParsing($recipient->getFullName()),
                [
                    'target_firstname' => self::escape($recipient->getFirstName() ?: ''),
                ]
            );
        }

        return $message;
    }

    public static function createChunk(array $recipients, ReferentMessage $original): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $chunk = clone $original;
        $chunk->uuid = Uuid::uuid4();
        $chunk->recipients = $recipients;

        return $chunk;
    }
}
