<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\Invite;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class InvitationMail extends TransactionalMail
{
    private const SUBJECT_PATTERN = '%s vous invite à rejoindre En Marche.';

    public static function createRecipient(Invite $invite): RecipientInterface
    {
        return new Recipient($invite->getEmail());
    }

    public static function createTemplateVars(Invite $invite): array
    {
        return [
            'sender_firstname' => StringCleaner::htmlspecialchars($invite->getFirstName()),
            'sender_lastname' => StringCleaner::htmlspecialchars($invite->getLastName()),
            'target_message' => nl2br(StringCleaner::htmlspecialchars($invite->getMessage())),
        ];
    }

    public static function createSubject(Invite $invite): string
    {
        return sprintf(self::SUBJECT_PATTERN, $invite->getSenderFullName());
    }
}
