<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CommitteeApprovalConfirmationMessage extends MailjetMessage
{
    public static function create(Adherent $host, string $committeeCityName, string $committeeUrl): self
    {
        return new static(
            Uuid::uuid4(),
            '54720',
            $host->getEmailAddress(),
            $host->getFullName(),
            'Confirmation de création de votre comité En Marche !',
            static::getTemplateVars($committeeCityName, $committeeUrl),
            static::getRecipientVars($host->getFirstName())
        );
    }

    private static function getTemplateVars(string $committeeCityName, string $committeeUrl): array
    {
        return [
            'animator_firstname' => '',
            'committee_city' => $committeeCityName,
            'committee_url' => $committeeUrl,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'animator_firstname' => $firstName,
        ];
    }
}
