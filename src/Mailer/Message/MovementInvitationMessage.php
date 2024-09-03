<?php

namespace App\Mailer\Message;

use App\Entity\Invite;

final class MovementInvitationMessage extends Message
{
    public static function createFromInvite(Invite $invite): self
    {
        return new self(
            $invite->getUuid(),
            $invite->getEmail(),
            null,
            \sprintf('%s vous invite à rejoindre En Marche.', self::escape($invite->getSenderFullName())),
            static::getTemplateVars($invite->getFirstName(), $invite->getLastName(), $invite->getMessage())
        );
    }

    private static function getTemplateVars(
        string $senderFirstName,
        string $senderLastName,
        string $targetMessage,
    ): array {
        return [
            'sender_firstname' => self::escape($senderFirstName),
            'sender_lastname' => self::escape($senderLastName),
            'target_message' => nl2br(self::escape($targetMessage)),
        ];
    }
}
