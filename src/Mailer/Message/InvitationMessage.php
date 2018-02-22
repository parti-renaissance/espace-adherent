<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Invite;

final class InvitationMessage extends Message
{
    public static function createFromInvite(Invite $invite): self
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
            'sender_firstname' => self::escape($invite->getFirstName()),
            'sender_lastname' => self::escape($invite->getLastName()),
            'target_message' => nl2br(self::escape($invite->getMessage())),
        ];
    }
}
