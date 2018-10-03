<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\PurchasingPowerInvitation;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
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
}

