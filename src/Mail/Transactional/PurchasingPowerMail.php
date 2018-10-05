<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\PurchasingPowerInvitation;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class PurchasingPowerMail extends TransactionalMail
{
    public static function createRecipientFor(PurchasingPowerInvitation $invitation): RecipientInterface
    {
        return new Recipient($invitation->getFriendEmailAddress());
    }

    public static function createTemplateVarsFrom(PurchasingPowerInvitation $invitation): array
    {
        return [
            'message' => StringCleaner::htmlspecialchars($invitation->getMailBody()),
        ];
    }

    public static function createSubjectFrom(PurchasingPowerInvitation $invitation): string
    {
        return $invitation->getMailSubject();
    }

    public static function createReplyToFrom(PurchasingPowerInvitation $invitation): RecipientInterface
    {
        return new Recipient($invitation->getAuthorEmailAddress());
    }

    public static function createSenderFrom(PurchasingPowerInvitation $invitation): SenderInterface
    {
        return new Sender(
            null,
            sprintf('%s %s', $invitation->getAuthorFirstName(), $invitation->getAuthorLastName())
        );
    }
}

