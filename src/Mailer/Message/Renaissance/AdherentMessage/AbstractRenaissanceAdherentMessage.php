<?php

namespace App\Mailer\Message\Renaissance\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Ramsey\Uuid\Uuid;

abstract class AbstractRenaissanceAdherentMessage extends AbstractRenaissanceMessage
{
    public static function create(AdherentMessageInterface $adherentMessage, array $adherents): self
    {
        /** @var Adherent[] $adherents */
        $first = array_shift($adherents);

        $message = new static(
            Uuid::uuid4(),
            $first ? $first->getEmailAddress() : '',
            $first ? $first->getFullName() : '',
            $adherentMessage->getSubject(),
            [],
            [],
            $adherentMessage->getAuthor()->getEmailAddress(),
            null,
            self::buildTemplateContent($adherentMessage)
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName());
        }

        return $message;
    }

    protected static function buildTemplateContent(AdherentMessageInterface $message): array
    {
        return [
            'content' => $message->getContent(),
            'reply_to_button' => sprintf(
                '<a class="mcnButton" title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $email = $message->getAuthor()->getEmailAddress()
            ),
            'reply_to_link' => sprintf(
                '<a title="Répondre" href="mailto:%s" target="_blank">Répondre</a>',
                $email
            ),
        ];
    }
}
