<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class GroupApprovalConfirmationMessage extends Message
{
    public static function create(Adherent $administrator): self
    {
        $message = new self(
            Uuid::uuid4(),
            '244444',
            $administrator->getEmailAddress(),
            $administrator->getFullName(),
            'Votre équipe MOOC est validée, à vous de jouer',
            static::getTemplateVars($groupCityName, $groupUrl),
            static::getRecipientVars($administrator->getFirstName())
        );

        $message->setVar('target_firstname', self::escape($administrator->getFirstName()));

        return $message;
    }
}
