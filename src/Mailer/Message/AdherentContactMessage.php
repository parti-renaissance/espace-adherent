<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Contact\ContactMessage;
use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentContactMessage extends Message
{
    public static function createFromModel(ContactMessage $contactMessage): self
    {
        $sender = $contactMessage->getFrom();
        $recipient = $contactMessage->getTo();

        return new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($sender, $recipient, $contactMessage->getContent()),
            [],
            $sender->getEmailAddress()
        );
    }

    private static function getTemplateVars(Adherent $sender, Adherent $recipient, string $message): array
    {
        return [
            'animator_firstname' => self::escape($recipient->getFirstName()),
            'member_firstname' => self::escape($sender->getFirstName()),
            'target_message' => \nl2br(self::escape($message)),
        ];
    }
}
