<?php

namespace AppBundle\Mailer\Message;

use AppBundle\BoardMember\BoardMemberMessage as BoardMemberMessageModel;
use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class BoardMemberMessage extends Message
{
    /**
     * @param BoardMemberMessageModel      $model
     * @param \AppBundle\Entity\Adherent[] $recipients
     *
     * @return BoardMemberMessage
     */
    public static function createFromModel(BoardMemberMessageModel $model, array $recipients): self
    {
        if (empty($recipients)) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \RuntimeException('First recipient must be an Adherent instance.');
        }

        $boardMember = $model->getFrom();

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            self::getTemplateVars($model->getContent()),
            [],
            $boardMember->getEmailAddress()
        );

        $message->setSenderEmail('jemarche@en-marche.fr');
        $message->setSenderName(sprintf('%s, membre du Conseil de LaREM', $boardMember->getFullName()));

        $message->addRecipient('jemarche@en-marche.fr', 'Je Marche');

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException('This message builder requires a collection of Adherent instances');
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName()
            );
        }

        return $message;
    }

    private static function getTemplateVars(string $message): array
    {
        return [
            'target_message' => $message,
        ];
    }
}
