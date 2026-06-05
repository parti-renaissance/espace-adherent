<?php

declare(strict_types=1);

namespace App\Mailer\Message\Campaign;

use App\Entity\Adherent;
use App\Mailer\Message\Message;
use App\Mailer\Message\Renaissance\AbstractRenaissanceMessage;
use Symfony\Component\Uid\Uuid;

class CampaignWelcomeMessage extends AbstractRenaissanceMessage
{
    public static function createFromAdherent(Adherent $adherent, string $magicLink): Message
    {
        return self::updateSenderInfo(new self(
            Uuid::v4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '',
            [
                'first_name' => self::escape($adherent->getFirstName()),
                'magic_link' => $magicLink,
            ]
        ));
    }
}
