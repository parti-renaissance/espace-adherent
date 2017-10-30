<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class GroupApprovalConfirmationMessage extends MailjetMessage
{
    public static function create(Adherent $administrator, string $groupCityName, string $groupUrl): self
    {
        return new self(
            Uuid::uuid4(),
            '54720', // TODO change ID
            $administrator->getEmailAddress(),
            self::fixMailjetParsing($administrator->getFullName()),
            'Votre équipe MOOC est validée, à vous de jouer',
            static::getTemplateVars($groupCityName, $groupUrl),
            static::getRecipientVars($administrator->getFirstName())
        );
    }

    private static function getTemplateVars(string $groupCityName, string $groupUrl): array
    {
        return [
            'animator_firstname' => '',
            'group_city' => $groupCityName,
            'group_url' => $groupUrl,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'animator_firstname' => self::escape($firstName),
        ];
    }
}
