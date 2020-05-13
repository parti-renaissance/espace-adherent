<?php

namespace App\Mailer\Message;

use App\Entity\NewsletterInvite;
use Ramsey\Uuid\Uuid;

final class NewsletterInvitationMessage extends Message
{
    public static function createFromInvite(NewsletterInvite $invite, string $subscribeUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $invite->getEmail(),
            null,
            sprintf('%s vous invite à vous abonner à la newsletter En Marche.', self::escape($invite->getSenderFullName())),
            static::getTemplateVars($invite->getFirstName(), $subscribeUrl)
        );
    }

    private static function getTemplateVars(string $firstName, string $subscribeLink): array
    {
        return [
            'sender_firstname' => self::escape($firstName),
            'subscribe_link' => $subscribeLink,
        ];
    }
}
