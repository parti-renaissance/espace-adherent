<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\NewsletterInvite;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class NewsletterInvitationMail extends TransactionalMail
{
    private const SUBJECT_PATTERN = '%s vous invite à vous abonner à la newsletter En Marche.';

    public static function createRecipient(NewsletterInvite $invite): RecipientInterface
    {
        return new Recipient($invite->getEmail());
    }

    public static function createTemplateVars(NewsletterInvite $invite, string $subscribeLink): array
    {
        return [
            'sender_firstname' => StringCleaner::htmlspecialchars($invite->getFirstName()),
            'subscribe_link' => $subscribeLink,
        ];
    }

    public static function createSubject(NewsletterInvite $invite): string
    {
        return sprintf(self::SUBJECT_PATTERN, $invite->getSenderFullName());
    }
}
