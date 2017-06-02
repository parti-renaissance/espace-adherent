<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Contact\ContactMessage;
use Ramsey\Uuid\Uuid;

final class AdherentContactMessage extends MailjetMessage
{
    /**
     * @param ContactMessage $contactMessage
     *
     * @return AdherentContactMessage
     */
    public static function createFromMdel(ContactMessage $contactMessage): self
    {
        return new self(
            Uuid::uuid4(),
            '114629',
            $contactMessage->getTo()->getEmailAddress(),
            self::fixMailjetParsing($contactMessage->getTo()->getFullName()),
            $contactMessage->getFrom()->getFirstName().' vous a envoyé un message',
            [],
            [
                'animator_firstname' => self::escape($contactMessage->getTo()->getFirstName()),
                'member_firstname' => self::escape($contactMessage->getFrom()->getFirstName()),
                'target_message' => nl2br(self::escape($contactMessage->getContent())),
            ],
            $contactMessage->getFrom()->getEmailAddress()
        );
    }
}
