<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class TonMacronFriendMail extends TransactionalMail
{
    public static function createRecipient(TonMacronFriendInvitation $invitation): RecipientInterface
    {
        return new Recipient($invitation->getFriendEmailAddress());
    }

    public static function createTemplateVars(TonMacronFriendInvitation $invitation): array
    {
        return [
            'message' => StringCleaner::htmlspecialchars($invitation->getMailBody()),
        ];
    }

    public static function createSubject(TonMacronFriendInvitation $invitation): string
    {
        return $invitation->getMailSubject();
    }

    public static function createReplyTo(TonMacronFriendInvitation $invitation): RecipientInterface
    {
        return new Recipient($invitation->getAuthorEmailAddress(), null);
    }

    public static function createSender(TonMacronFriendInvitation $invitation): SenderInterface
    {
        return new Sender(
            null,
            sprintf('%s %s', $invitation->getAuthorFirstName(), $invitation->getAuthorLastName())
        );
    }

    public static function createCcRecipients(TonMacronFriendInvitation $invitation): array
    {
        return [new Recipient($invitation->getAuthorEmailAddress())];
    }
}
