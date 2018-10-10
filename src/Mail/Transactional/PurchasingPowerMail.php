<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\PurchasingPowerInvitation;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class PurchasingPowerMail extends TransactionalMail
{
    public static function createRecipient(PurchasingPowerInvitation $invitation): RecipientInterface
    {
        return new Recipient($invitation->getFriendEmailAddress());
    }

    public static function createTemplateVars(PurchasingPowerInvitation $invitation): array
    {
        return [
            'message' => StringCleaner::htmlspecialchars($invitation->getMailBody()),
        ];
    }

    public static function createSubject(PurchasingPowerInvitation $invitation): string
    {
        return $invitation->getMailSubject();
    }

    public static function createReplyTo(PurchasingPowerInvitation $invitation): RecipientInterface
    {
        return new Recipient($invitation->getAuthorEmailAddress());
    }

    public static function createSender(PurchasingPowerInvitation $invitation): SenderInterface
    {
        return new Sender(
            null,
            sprintf('%s %s', $invitation->getAuthorFirstName(), $invitation->getAuthorLastName())
        );
    }

    public static function createCcRecipients(PurchasingPowerInvitation $invitation): array
    {
        return [new Recipient($invitation->getAuthorEmailAddress())];
    }
}
