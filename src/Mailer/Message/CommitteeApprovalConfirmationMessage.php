<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CommitteeApprovalConfirmationMessage extends Message
{
    public static function create(Adherent $host, string $committeeCityName): self
    {
        return new self(
            Uuid::uuid4(),
            $host->getEmailAddress(),
            $host->getFullName(),
            self::getTemplateVars($committeeCityName),
            self::getRecipientVars($host->getFirstName())
        );
    }

    private static function getTemplateVars(string $committeeCityName): array
    {
        return [
            'committee_city' => $committeeCityName,
        ];
    }

    private static function getRecipientVars(string $firstName): array
    {
        return [
            'animator_firstname' => self::escape($firstName),
        ];
    }
}
