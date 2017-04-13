<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\NewsletterInvite;
use Ramsey\Uuid\Uuid;

final class NewsletterInvitationMessage extends MailjetMessage
{
    public static function createFromInvite(NewsletterInvite $invite, string $subscribeUrl): self
    {
        return new self(
            Uuid::uuid4(),
            '120780',
            $invite->getEmail(),
            null,
            sprintf('%s vous invite à vous abonner à la newsletter En Marche.', self::escape($invite->getSenderFullName())),
            static::getTemplateVars($invite->getFirstName(), $subscribeUrl),
            [],
            null,
            $invite->getUuid()
        );
    }

    private static function getTemplateVars(string $firstName, string $subscribeLink)
    {
        return [
            'sender_firstname' => self::escape($firstName),
            'subscribe_link' => $subscribeLink,
        ];
    }
}
