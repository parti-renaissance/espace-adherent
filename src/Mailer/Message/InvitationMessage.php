<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Invite;

final class InvitationMessage extends Message
{
    public static function create(Invite $invite): self
    {
        return new self(
            $invite->getUuid(),
            $invite->getEmail(),
            null,
            static::getTemplateVars($invite)
        );
    }

    private static function getTemplateVars(Invite $invite): array
    {
        return [
            'sender_first_name' => self::escape($invite->getFirstName()),
            'sender_last_name' => self::escape($invite->getLastName()),
            'message' => nl2br(self::escape($invite->getMessage())),
        ];
    }
}
