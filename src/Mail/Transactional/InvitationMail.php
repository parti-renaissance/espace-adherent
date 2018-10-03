<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Invite;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class InvitationMail extends TransactionalMail
{
    const SUBJECT = '%s vous invite à rejoindre En Marche.';

    public static function createRecipientFor(Invite $invite): RecipientInterface
    {
        return new Recipient($invite->getEmail());
    }

    public static function createTemplateVarsFrom(Invite $invite): array
    {
        return [
            'sender_firstname' => StringCleaner::htmlspecialchars($invite->getFirstName()),
            'sender_lastname' => StringCleaner::htmlspecialchars($invite->getLastName()),
            'target_message' => nl2br(StringCleaner::htmlspecialchars($invite->getMessage())),
        ];
    }

    public static function createSubjectFor(Invite $invite): string
    {
        return sprintf(self::SUBJECT, $invite->getSenderFullName());
    }
}
