<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\NewsletterInvite;
use Ramsey\Uuid\Uuid;

final class NewsletterInvitationMessage extends Message
{
    public static function create(NewsletterInvite $invite, string $subscribeUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $invite->getEmail(),
            null,
            static::getTemplateVars($invite, $subscribeUrl)
        );
    }

    private static function getTemplateVars(NewsletterInvite $invite, string $subscribeLink): array
    {
        return [
            'sender_first_name' => self::escape($invite->getFirstName()),
            'subscribe_link' => $subscribeLink,
        ];
    }
}
