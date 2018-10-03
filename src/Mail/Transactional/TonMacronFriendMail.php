<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class TonMacronFriendMail extends TransactionalMail
{
    public static function createRecipientFor(TonMacronFriendInvitation $invitation): RecipientInterface
    {
        return new Recipient($invitation->getFriendEmailAddress());
    }

    public static function createTemplateVarsFrom(TonMacronFriendInvitation $invitation): array
    {
        return [
            'message' => StringCleaner::htmlspecialchars($invitation->getMailBody()),
        ];
    }
}

