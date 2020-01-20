<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CommitteeApprovalConfirmationMessage extends Message
{
    public static function create(Adherent $host, string $committeeCityName, string $committeeUrl): self
    {
        return new self(
            Uuid::uuid4(),
            $host->getEmailAddress(),
            $host->getFullName(),
            'Votre comité est validé, à vous de jouer',
            static::getTemplateVars($committeeCityName, $committeeUrl),
            static::getRecipientVars($host->getFirstName())
        );
    }

    private static function getTemplateVars(string $committeeCityName, string $committeeUrl): array
    {
        return [
            'committee_city' => $committeeCityName,
            'committee_url' => $committeeUrl,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'animator_firstname' => self::escape($firstName),
        ];
    }
}
