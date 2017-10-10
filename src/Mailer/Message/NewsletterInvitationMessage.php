<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\NewsletterInvite;
use Ramsey\Uuid\Uuid;

final class NewsletterInvitationMessage extends Message
{
    public static function createFromInvite(NewsletterInvite $invite, string $subscribeUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $invite->getEmail(),
            null,
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
