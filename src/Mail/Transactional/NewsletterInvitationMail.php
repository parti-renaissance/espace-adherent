<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\NewsletterInvite;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

class NewsletterInvitationMail extends TransactionalMail
{
    const SUBJECT = '%s vous invite à vous abonner à la newsletter En Marche.';

    public static function createRecipientFor(NewsletterInvite $invite): RecipientInterface
    {
        return new Recipient($invite->getEmail());
    }

    public static function createTemplateVarsFrom(NewsletterInvite $invite, string $subscribeLink): array
    {
        return [
            'sender_firstname' => StringCleaner::htmlspecialchars($invite->getFirstName()),
            'subscribe_link' => $subscribeLink,
        ];
    }

    public static function createSubjectFor(NewsletterInvite $invite): string
    {
        return sprintf(self::SUBJECT, $invite->getSenderFullName());
    }
}
