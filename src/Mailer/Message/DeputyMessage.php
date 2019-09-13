<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Deputy\DeputyMessage as DeputyMessageModel;
use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class DeputyMessage extends Message
{
    /**
     * @param Adherent[] $recipients
     *
     * @return DeputyMessage
     */
    public static function create(DeputyMessageModel $model, array $recipients): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $deputy = $model->getFrom();
        $first = array_shift($recipients);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName() ?: '',
            $model->getSubject(),
            [
                'deputy_fullname' => self::escape($deputy->getFullName()),
                'circonscription_name' => self::escape($deputy->getManagedDistrict()),
                'target_message' => $model->getContent(),
            ],
            [
                'target_firstname' => self::escape($first->getFirstName() ?: ''),
            ],
            'ne-pas-repondre@en-marche.fr'
        );

        $message->setSenderEmail('jemarche@en-marche.fr');
        $message->setSenderName($deputy->getFullName().' [Député]');

        foreach ($recipients as $recipient) {
            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName() ?: '',
                [
                    'target_firstname' => self::escape($recipient->getFirstName() ?: ''),
                ]
            );
        }

        return $message;
    }
}
