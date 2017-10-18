<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\BoardMember\BoardMemberMessage as BoardMemberMessageModel;
use Ramsey\Uuid\Uuid;

final class BoardMemberMessage extends MailjetMessage
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

        $boardMember = $model->getFrom();
        $first = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            '233701',
            $first->getEmailAddress(),
            self::fixMailjetParsing($first->getFullName() ?: ''),
            $model->getSubject(),
            [
                'member_firstname' => self::escape($boardMember->getFirstName()),
                'member_lastname' => self::escape($boardMember->getLastName()),
                'target_message' => $model->getContent(),
            ],
            [],
            $boardMember->getEmailAddress()
        );

        $message->setSenderEmail('jemarche@en-marche.fr');
        $message->setSenderName(sprintf('%s, membre du Conseil de LaREM', $boardMember->getFullName()));

        $message->addRecipient('jemarche@en-marche.fr', 'Je Marche');

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                self::fixMailjetParsing($recipient->getFullName() ?: '')
            );
        }

        return $message;
    }
}
